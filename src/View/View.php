<?php declare(strict_types = 1);

namespace App\View;

class View {
    public static function render(string $template, array $data = []) : void {
        extract($data);

        $templatePath = BASE_PATH . "templates/{$template}.php";
        if(!file_exists($templatePath)) {
            throw new \RuntimeException("Template non trovato: {$template}");
        }

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        require BASE_PATH . 'templates/layout/page-layout.php';
        
        require BASE_PATH . 'templates/layout/header.php';
        echo $content;

        if (file_exists(BASE_PATH . 'templates/layout/footer.php')) {
            require BASE_PATH . 'templates/layout/footer.php';
        }
    }

    public static function renderJson(mixed $data, int $status = 200) {
        http_response_code($status);
        header('Content-type: application/json');
        echo json_encode($data);
    }
}