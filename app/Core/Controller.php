<?php
namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        echo $content;
    }

    protected function layout(string $layout, string $view, array $data = []): void
    {
        $data['_view'] = $view;
        extract($data);

        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewFile;
        $viewContent = ob_get_clean();

        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layout}");
        }

        $data['content'] = $viewContent;
        extract($data);
        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url, array $flash = []): void
    {
        foreach ($flash as $key => $value) {
            Session::flash($key, $value);
        }
        header('Location: ' . $url);
        exit;
    }

    protected function back(array $flash = []): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer, $flash);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = $this->checkRule($field, $value, $rule, $params, $data);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        return $errors;
    }

    private function checkRule(string $field, mixed $value, string $rule, array $params, array $data): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        return match ($rule) {
            'required' => empty($value) && $value !== '0' ? "{$label} is required" : null,
            'email' => $value && !filter_var($value, FILTER_VALIDATE_EMAIL) ? "{$label} must be a valid email" : null,
            'min' => $value && strlen($value) < (int) $params[0] ? "{$label} must be at least {$params[0]} characters" : null,
            'max' => $value && strlen($value) > (int) $params[0] ? "{$label} must not exceed {$params[0]} characters" : null,
            'numeric' => $value && !is_numeric($value) ? "{$label} must be numeric" : null,
            'confirmed' => $value !== ($data[$field . '_confirmation'] ?? null) ? "{$label} confirmation does not match" : null,
            'unique' => $this->checkUnique($field, $value, $params) ? "{$label} already exists" : null,
            'phone' => $value && !preg_match('/^\+?[1-9]\d{6,14}$/', preg_replace('/[\s\-\(\)]/', '', $value)) ? "{$label} must be a valid phone number" : null,
            default => null
        };
    }

    private function checkUnique(string $field, mixed $value, array $params): bool
    {
        if (!$value || empty($params[0])) return false;
        $table = $params[0];
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$column} = ?";
        $sqlParams = [$value];
        if ($exceptId) {
            $sql .= " AND id != ?";
            $sqlParams[] = $exceptId;
        }
        $result = Database::fetch($sql, $sqlParams);
        return ($result['cnt'] ?? 0) > 0;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function allInput(): array
    {
        $json = json_decode(file_get_contents('php://input'), true);
        return array_merge($_GET, $_POST, $json ?? []);
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
