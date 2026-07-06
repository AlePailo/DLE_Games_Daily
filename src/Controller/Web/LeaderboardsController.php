<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;

class LeaderboardsController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $this->render('leaderboards', [
            'title' => 'Leaderboards | DLE Games Daily'
        ]);
    }
}