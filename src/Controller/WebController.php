<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;

class WebController extends BaseController {

    protected function render(string $template, array $data = [], bool $requiresNav = true) : void {
        
        // Structural data
        $data['csrfToken']  = $this->sessionManager->getCsrfToken();
        $data['isLoggedIn'] = $this->sessionManager->isLoggedIn();

        // Flash data
        $error = $this->sessionManager->getFlash('error');
        $flashData = [
            'error'   => $error != null ? (array) $error : null,
            'success' => $this->sessionManager->getFlash('success'),
            'info'    => $this->sessionManager->getFlash('info'),
            'old'     => $this->sessionManager->getFlash('oldInput'),
            'show_resend' => $this->sessionManager->getFlash('show_resend')
        ];

        // CSS
        $defaultCss = ['base.css', 'layout/app-shell.css'];
        if($requiresNav) {
            $defaultCss[] = 'layout/navigation.css';
        }
        $data['css'] = array_values(array_unique([...$defaultCss, ...($data['css'] ?? [])]));

        // JS
        $data['js'] = array_values(array_unique(['base.js', ...($data['js'] ?? [])]));

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
        throw new \App\Exception\NotFoundException('Page not found');
    }
}