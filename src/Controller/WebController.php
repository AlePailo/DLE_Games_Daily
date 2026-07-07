<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;
use App\Core\SessionManager;

class WebController extends BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    protected function render(string $template, array $data = [], bool $requiresNav = true) : void {
        $defaultCss = ['base.css', 'layout/app-shell.css'];

        $flashData = [
            'error'   => $this->sessionManager->getFlash('error'),
            'success' => $this->sessionManager->getFlash('success'),
            'info'    => $this->sessionManager->getFlash('info'),
            'old'     => $this->sessionManager->getFlash('oldInput'),
            'csrf_token' => $this->sessionManager->getCsrfToken(),
        ];

        $data = array_merge($flashData, $data);

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

    protected function redirect(string $url) : void {
        //header("Location: /DLE_Games_Daily/{$url}");
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
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