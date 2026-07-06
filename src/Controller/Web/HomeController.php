<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;

class HomeController extends WebController {
    public function __construct(
        SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {

        $this->render('home', [
            'title' => 'Home | DLE Games Daily'
        ]);
    }
}