<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;
use App\View\View;
use App\Model\Repository\IFranchiseRepository;

class HomeController extends BaseController {
    public function __construct(
        private IFranchiseRepository $franchises,
        SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $franchises = $this->franchises->findAll();

        $this->render('home', [
            'title' => 'Home | DLE Games Daily',
            'franchises' => $franchises,
            /*'css' => ['home.css'],
            'js' => ['home.js']*/
        ]);
    }
}