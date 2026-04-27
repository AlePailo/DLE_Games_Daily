<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;

abstract class BaseController {
    protected function render(string $template, array $data = []) : void {
        View::render($template, $data);
    }

    protected function renderJson(mixed $data, int $status) : void {
        View::renderJson($data, $status);
    }

    protected function redirect(string $url) : void {
        header("Location: /DLE_Games_Daily/{$url}");
        exit;
    }

    protected function getJsonBody() : array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function notFound() : void {
        http_response_code(404);
        View::render('404/index', [
            'title' => 'Page not found | DLE Games Daily',
            'css' => [],
            'js' => []
        ]);
        exit;
    }
}