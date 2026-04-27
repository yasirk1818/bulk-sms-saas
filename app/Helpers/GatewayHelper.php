<?php
namespace App\Helpers;

use App\Core\Database;
use App\Core\Logger;

class GatewayHelper
{
    public static function send(array $gateway, string $recipient, string $message, ?string $senderId = null): array
    {
        $startTime = microtime(true);

        try {
            $result = match ($gateway['type']) {
                'http_get' => self::sendHttpGet($gateway, $recipient, $message, $senderId),
                'http_post' => self::sendHttpPost($gateway, $recipient, $message, $senderId),
                'json_api' => self::sendJsonApi($gateway, $recipient, $message, $senderId),
                default => ['success' => false, 'error' => 'Unsupported gateway type']
            };

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Update gateway stats
            $updateData = ['avg_response_time' => $responseTime];
            if ($result['success']) {
                $updateData['total_sent'] = Database::fetch("SELECT total_sent FROM gateways WHERE id = ?", [$gateway['id']])['total_sent'] + 1;
                $updateData['last_success_at'] = date('Y-m-d H:i:s');
            } else {
                $updateData['total_failed'] = Database::fetch("SELECT total_failed FROM gateways WHERE id = ?", [$gateway['id']])['total_failed'] + 1;
                $updateData['last_failure_at'] = date('Y-m-d H:i:s');
                $updateData['last_error'] = $result['error'] ?? 'Unknown error';
            }
            Database::update('gateways', $updateData, 'id = ?', [$gateway['id']]);

            $result['response_time'] = $responseTime;
            return $result;

        } catch (\Exception $e) {
            Logger::error("Gateway {$gateway['name']} error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private static function sendHttpGet(array $gateway, string $recipient, string $message, ?string $senderId): array
    {
        $params = [];
        $extraParams = json_decode($gateway['extra_params'] ?? '{}', true) ?: [];

        if ($gateway['api_key']) $params['apikey'] = $gateway['api_key'];
        if ($gateway['recipient_param']) $params[$gateway['recipient_param']] = $recipient;
        if ($gateway['message_param']) $params[$gateway['message_param']] = $message;
        if ($senderId && $gateway['sender_id_param']) $params[$gateway['sender_id_param']] = $senderId;

        $params = array_merge($params, $extraParams);
        $url = $gateway['api_url'] . '?' . http_build_query($params);

        $response = self::httpRequest('GET', $url, null, self::buildHeaders($gateway));
        return self::parseResponse($gateway, $response);
    }

    private static function sendHttpPost(array $gateway, string $recipient, string $message, ?string $senderId): array
    {
        $params = [];
        $extraParams = json_decode($gateway['extra_params'] ?? '{}', true) ?: [];

        if ($gateway['api_key']) $params['apikey'] = $gateway['api_key'];
        if ($gateway['recipient_param']) $params[$gateway['recipient_param']] = $recipient;
        if ($gateway['message_param']) $params[$gateway['message_param']] = $message;
        if ($senderId && $gateway['sender_id_param']) $params[$gateway['sender_id_param']] = $senderId;

        $params = array_merge($params, $extraParams);

        $response = self::httpRequest('POST', $gateway['api_url'], http_build_query($params), array_merge(
            ['Content-Type: application/x-www-form-urlencoded'],
            self::buildHeaders($gateway)
        ));
        return self::parseResponse($gateway, $response);
    }

    private static function sendJsonApi(array $gateway, string $recipient, string $message, ?string $senderId): array
    {
        $body = [];
        $extraParams = json_decode($gateway['extra_params'] ?? '{}', true) ?: [];

        if ($gateway['recipient_param']) $body[$gateway['recipient_param']] = $recipient;
        if ($gateway['message_param']) $body[$gateway['message_param']] = $message;
        if ($senderId && $gateway['sender_id_param']) $body[$gateway['sender_id_param']] = $senderId;

        $headers = array_merge(['Content-Type: application/json'], self::buildHeaders($gateway));

        // Handle auth types
        $authType = $extraParams['auth_type'] ?? 'header';
        if ($authType === 'basic' && $gateway['api_key'] && $gateway['api_secret']) {
            $headers[] = 'Authorization: Basic ' . base64_encode($gateway['api_key'] . ':' . $gateway['api_secret']);
        } elseif ($authType === 'bearer' && $gateway['api_key']) {
            $headers[] = 'Authorization: Bearer ' . $gateway['api_key'];
        }

        // URL replacements (e.g., Twilio account SID in URL)
        $url = $gateway['api_url'];
        if (!empty($extraParams['account_sid'])) {
            $url = str_replace('{ACCOUNT_SID}', $extraParams['account_sid'], $url);
        }
        if (!empty($extraParams['auth_id'])) {
            $url = str_replace('{AUTH_ID}', $extraParams['auth_id'], $url);
        }

        $response = self::httpRequest('POST', $url, json_encode($body), $headers);
        return self::parseResponse($gateway, $response);
    }

    private static function buildHeaders(array $gateway): array
    {
        $headers = [];
        $customHeaders = json_decode($gateway['headers'] ?? '{}', true) ?: [];
        foreach ($customHeaders as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
        return $headers;
    }

    private static function httpRequest(string $method, string $url, ?string $body = null, array $headers = []): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'body' => $response,
            'http_code' => $httpCode,
            'error' => $error
        ];
    }

    private static function parseResponse(array $gateway, array $response): array
    {
        if (!empty($response['error'])) {
            return ['success' => false, 'error' => 'cURL error: ' . $response['error'], 'raw' => $response];
        }

        $httpCode = $response['http_code'];
        $body = $response['body'];

        if ($httpCode >= 200 && $httpCode < 300) {
            $json = json_decode($body, true);
            $messageId = null;

            if (is_array($json)) {
                $messageId = $json['sid'] ?? $json['message_id'] ?? $json['messageId'] ??
                             $json['messages'][0]['message-id'] ?? $json['messages'][0]['messageId'] ??
                             $json['id'] ?? null;
            }

            return [
                'success' => true,
                'message_id' => $messageId,
                'response' => $body,
                'http_code' => $httpCode
            ];
        }

        return [
            'success' => false,
            'error' => "HTTP {$httpCode}: {$body}",
            'http_code' => $httpCode,
            'response' => $body
        ];
    }

    public static function checkHealth(array $gateway): array
    {
        $startTime = microtime(true);
        try {
            $ch = curl_init($gateway['api_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            curl_close($ch);

            $status = ($httpCode >= 200 && $httpCode < 500) ? 'up' : 'down';
            return ['status' => $status, 'response_time' => $responseTime, 'http_code' => $httpCode];
        } catch (\Exception $e) {
            return ['status' => 'down', 'response_time' => 0, 'error' => $e->getMessage()];
        }
    }
}
