<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;

class ProfileController extends BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $this->render('profile', [
            'title' => 'Profile | DLE Games Daily'
        ]);
    }
}