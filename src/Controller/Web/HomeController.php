<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Repository\IFranchiseRepository;

class HomeController extends WebController {
    public function __construct(
        private IFranchiseRepository $franchiseRepo,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {

        $userId = $this->sessionManager->getUserId();
        if($userId !== null) {
            $favouriteFranchises = $this->franchiseRepo->findAllFavouritesByUser($userId);
        }

        $this->render('home', [
            'title' => 'Home | DLE Games Daily',
            'favourites' => $favouriteFranchises ?? '',
            'css' => ['home.css', 'franchises.css'],
            'js' => ['home.js']
        ]);
    }
}