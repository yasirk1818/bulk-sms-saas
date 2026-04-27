<?php
namespace App\Middleware;

use App\Core\Logger;

class RateLimitMiddleware
{
    public function handle(): void
    {
        if (!defined('RATE_LIMIT_ENABLED') || !RATE_LIMIT_ENABLED) {
            return;
        }

        $ip = Logger::getIp();
        $key = 'rate_limit_' . md5($ip);
        $cacheFile = STORAGE_PATH . '/cache/' . $key . '.json';

        $maxRequests = defined('RATE_LIMIT_REQUESTS') ? RATE_LIMIT_REQUESTS : 60;
        $window = defined('RATE_LIMIT_WINDOW') ? RATE_LIMIT_WINDOW : 60;

        $data = ['count' => 0, 'reset_at' => time() + $window];
        if (file_exists($cacheFile)) {
            $stored = json_decode(file_get_contents($cacheFile), true);
            if ($stored && $stored['reset_at'] > time()) {
                $data = $stored;
            }
        }

        $data['count']++;

        if ($data['count'] > $maxRequests) {
            http_response_code(429);
            header('Retry-After: ' . ($data['reset_at'] - time()));
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            exit;
        }

        @file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    }
}
