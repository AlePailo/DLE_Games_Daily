<?php declare(strict_types = 1);

use App\Core\SessionManager;
use App\Service\AuthService;
use App\Core\Mailer;
use App\Model\Repository\FranchiseRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\CharacterRepository;
use App\Model\Repository\DailyChallengeRepository;
use App\Model\Repository\GameAttemptRepository;
use App\Model\Repository\GameAttemptResultRepository;
use App\Model\Repository\GameSessionRepository;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IGameAttemptResultRepository;
use App\Model\Repository\IGameSessionRepository;
use App\Model\Repository\IUserFavouritesRepository;
use App\Model\Repository\IUserFranchiseStatsRepository;
use App\Model\Repository\UserRepository;
use App\Model\Repository\IUserRepository;
use App\Model\Repository\UserFavouritesRepository;
use App\Model\Repository\UserFranchiseStatsRepository;
use App\Service\CharacterComparisonService;
use App\Service\GameSessionService;

return [
    // Infrastructure
    \PDO::class => function() {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_CHARSET']);
        
        return new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES     => false
        ]);
    },


    /* ----- AUTOWIRING ----- */

    // Repositories
    IFranchiseRepository::class             => \DI\autowire(FranchiseRepository::class),
    ICharacterRepository::class             => \DI\autowire(CharacterRepository::class),
    IUserRepository::class                  => \DI\autowire(UserRepository::class),
    IUserFranchiseStatsRepository::class    => \DI\autowire(UserFranchiseStatsRepository::class),
    IGameSessionRepository::class           => \DI\autowire(GameSessionRepository::class),
    IGameAttemptRepository::class           => \DI\autowire(GameAttemptRepository::class),
    IGameAttemptResultRepository::class     => \DI\autowire(GameAttemptResultRepository::class),
    IDailyChallengeRepository::class        => \DI\autowire(DailyChallengeRepository::class),
    IUserFavouritesRepository::class    => \DI\autowire(UserFavouritesRepository::class),

    // Core
    SessionManager::class   => \DI\autowire(SessionManager::class),
    Mailer::class           => \DI\autowire(Mailer::class),

    // Services
    AuthService::class                  => \DI\autowire(AuthService::class),
    GameSessionService::class           => \DI\autowire(GameSessionService::class),
    CharacterComparisonService::class   => \DI\autowire(CharacterComparisonService::class)
];