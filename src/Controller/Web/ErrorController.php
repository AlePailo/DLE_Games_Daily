<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;

class ErrorController extends WebController {
    public function notFound() : void {
        http_response_code(404);

        $this->render('errors/404', [
            'title' => 'Page not found | DLE Games Daily',
            'css' => ['errors.css'],
            'js' => []
        ]);
    }
}