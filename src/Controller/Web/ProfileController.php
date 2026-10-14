<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;

class ProfileController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $userId = $this->sessionManager->getUserId();
        if($userId === null) {
            $this->redirect('login');
            return;
        }

        $this->render('profile', [
            'title' => 'Profile | DLE Games Daily',
            'css' => [],
            'js' => []
        ]);
    }
}