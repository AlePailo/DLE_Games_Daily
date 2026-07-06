<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Model\Repository\IUserFavouritesRepository;

class FavouriteApiController extends ApiController {
    public function __construct(
        private IUserFavouritesRepository $userFavourites,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function toggle(array $vars) : void {
        $userId = $this->sessionManager->getUserId();

        if($userId === null) {
            $this->renderJson([
                'success' => false, 
                'error' => 'Login required'
            ], 401);
            return;
        }

        $body = $this->getJsonBody();
        var_dump($body);
        $franchiseId = (int)($body['franchise_id'] ?? 0);

        if($franchiseId <= 0) {
            $this->renderJson([
                'success' => false,
                'error' => 'Invalid ID'
            ], 400);
            return;
        }

        $isFav = $this->userFavourites->isFavourite($userId, $franchiseId);

        if($isFav) {
            $this->userFavourites->remove($userId, $franchiseId);
            $isFavNow = false;
        } else {
            $this->userFavourites->add($userId, $franchiseId);
            $isFavNow = true;
        }

        $this->renderJson([
            'success' => true,
            'is_favourite' => $isFavNow
        ]);
    }
}