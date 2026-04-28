<?php
namespace App\Core;

class Application
{
    private Router $router;
    private static ?Application $instance = null;

    public function __construct()
    {
        self::$instance = $this;
        $this->init();
    }

    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    private function init(): void
    {
        // Error handling
        error_reporting(E_ALL);
        ini_set('display_errors', APP_DEBUG ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', STORAGE_PATH . '/logs/error.log');

        // Timezone
        date_default_timezone_set(APP_TIMEZONE);

        // Start session
        Session::start();

        // Initialize CSRF
        Csrf::init();

        // Initialize router
        $this->router = new Router();
        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        $routeFile = APP_PATH . '/Config/routes.php';
        if (file_exists($routeFile)) {
            $router = $this->router;
            require_once $routeFile;
        }
    }

    public function run(): void
    {
        try {
            // Apply global middleware
            $middlewares = [
                new \App\Middleware\RateLimitMiddleware(),
                new \App\Middleware\MaintenanceMiddleware(),
            ];

            foreach ($middlewares as $middleware) {
                $middleware->handle();
            }

            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): void
    {
        Logger::error('Application Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if (APP_DEBUG) {
            http_response_code(500);
            echo '<h1>Application Error</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            http_response_code(500);
            include APP_PATH . '/Views/errors/500.php';
        }
    }
}
