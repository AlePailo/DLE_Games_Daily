<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Service\UserFranchiseStatsService;

class LeaderboardsApiController extends ApiController {
    public function __construct(
        protected SessionManager $sessionManager,
        private UserFranchiseStatsService $statsService
    ) {
        parent::__construct($sessionManager);
    }

    public function fetch(array $vars) : void {
        $metric = $_GET['metric'];
        $franchise = $_GET['franchise'];

        $leaderboard =  $this->statsService->getLeaderboard($franchise, $metric);

        $this->renderJson([
            'success' => true,
            'data' => $leaderboard
        ]);
    }
}