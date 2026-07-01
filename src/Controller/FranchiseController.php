<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;
use App\Model\Repository\IFranchiseRepository;

class FranchiseController extends BaseController {
    public function __construct(
        private IFranchiseRepository $franchises,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $franchises = $this->franchises->findAll();

        $this->render('games', [
            'title' => 'Games | DLE Games Daily',
            'franchises' => $franchises
        ]);
    }
}