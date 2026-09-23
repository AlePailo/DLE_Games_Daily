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
        
        // Structural data
        $data['csrfToken']  = $this->sessionManager->getCsrfToken();
        $data['isLoggedIn'] = $this->sessionManager->isLoggedIn();

        // Flash data
        $flashData = [
            'error'   => $this->sessionManager->getFlash('error'),
            'success' => $this->sessionManager->getFlash('success'),
            'info'    => $this->sessionManager->getFlash('info'),
            'old'     => $this->sessionManager->getFlash('oldInput')
        ];

        // CSS
        $defaultCss = ['base.css', 'layout/app-shell.css'];
        if($requiresNav) {
            $defaultCss[] = 'layout/navigation.css';
        }
        $data['css'] = array_merge($defaultCss, $data['css']) ?? [];

        // JS
        $defaultJs = ['base.js'];
        $data['js'] = array_merge($defaultJs, $data['js']) ?? [];

        // Final merge
        $data = array_merge(
            $this->sessionManager->getSessionData(),
            $flashData,
            $data
        );

        View::render($template, $data, $requiresNav);
    }

    protected function redirect(string $url) : void {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    protected function notFound() : void {
        http_response_code(404);
        View::render('404', [
            'title' => 'Page not found | DLE Games Daily',
            'isLoggedIn' => $this->sessionManager->isLoggedIn(),
            'csrfToken'  => $this->sessionManager->getCsrfToken() ?? '',
            'css' => [],
            'js' => []
        ]);
        exit;
    }
}