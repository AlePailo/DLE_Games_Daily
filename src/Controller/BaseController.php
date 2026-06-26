<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;
use App\Core\SessionManager;

abstract class BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {}

    protected function render(string $template, array $data = [], bool $requiresNav = true) : void {
        $defaultCss = ['reset.css', 'base.css', 'layout/app-shell.css'];

        if($requiresNav) {
            $defaultCss[] = 'layout/navigation.css';
        }

        if(isset($data['css'])) {
            $data['css'] = array_merge($defaultCss, $data['css']);
        } else {
            $data['css'] = $defaultCss;
        }


        $defaultJs = ['base.js', 'utils/alerts.js'];

        if(isset($data['js'])) {
            $data['js'] = array_merge($defaultJs, $data['js']);
        } else {
            $data['js'] = $defaultJs;
        }

        $data = array_merge($this->sessionManager->getSessionData(), $data);

        View::render($template, $data, $requiresNav);
    }

    protected function renderJson(mixed $data, int $status = 200) : void {
        View::renderJson($data, $status);
    }

    protected function redirect(string $url) : void {
        header("Location: /DLE_Games_Daily/{$url}");
        //header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    protected function getJsonBody() : array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function notFound() : void {
        http_response_code(404);
        View::render('404', [
            'title' => 'Page not found | DLE Games Daily',
            'css' => [],
            'js' => []
        ]);
        exit;
    }
}