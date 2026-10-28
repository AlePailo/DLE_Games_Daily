<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Repository\FranchiseRepository;
use App\Service\UserFranchiseStatsService;

class LeaderboardsController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager,
        private UserFranchiseStatsService $statsService,
        private FranchiseRepository $franchiseRepository
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $metric = $_GET['metric'] ?? 'avg_attempts';
        $franchise = $_GET['franchise'] ?? 'global';

        $leaderboard = $this->statsService->getLeaderboard($franchise, $metric);
        $franchises = $this->franchiseRepository->findAll();

        $this->render('leaderboards', [
            'title' => 'Leaderboards | DLE Games Daily',
            'leaderboard' => $leaderboard,
            'franchises' => $franchises,
            'metric' => $metric,
            'franchise' => $franchise,
            'css' => ['leaderboards.css'],
            'js' => ['leaderboards.js']
        ]);
    }
}