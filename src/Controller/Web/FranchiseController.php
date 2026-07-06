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
        $franchisesAll = $this->franchises->findAll();

        $userId = $this->sessionManager->getUserId();
        $favouriteIds = $userId ? $this->userFavourites->findFranchiseIdsByUser($userId) : [];
        $isGuest = ($userId === null);
        $franchises = array_map(function($franchise) use ($favouriteIds, $isGuest) {
            return [
                'id' => $franchise->getId(),
                'name' => $franchise->getName(),
                'slug' => $franchise->getSlug(),
                'isActive' => $franchise->getIsActive(),
                'icon_url' => $franchise->getIconUrl(),
                'banner_url' => $franchise->getBgImageUrl(),
                'is_new' => $franchise->isNew(),
                'is_favourite' => !$isGuest && in_array($franchise->getId(), $favouriteIds, true)
            ];
        }, $franchisesAll);

        $this->render('games', [
            'title' => 'Games | DLE Games Daily',
            'franchises' => $franchises,
            'isGuest' => $isGuest,
            'css' => ['games.css'],
            'js' => ['games.js']
        ]);
    }
}