<?php declare(strict_types = 1);

namespace App\Controller\web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IUserFavouritesRepository;

class FranchiseController extends WebController {
    public function __construct(
        private IFranchiseRepository $franchises,
        private IUserFavouritesRepository $userFavourites,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $franchises = $this->franchises->findAll();

        $userId = $this->sessionManager->getUserId();
        $favouriteIds = $userId ? $this->userFavourites->findFranchiseIdsByUser($userId) : [];
        $isGuest = ($userId === null);

        $this->render('games', [
            'title' => 'Games | DLE Games Daily',
            'franchises' => $franchises,
            'favouriteIds' => $favouriteIds,
            'css' => ['games.css', 'franchises.css'],
            'js' => ['games.js']
        ]);
    }
}