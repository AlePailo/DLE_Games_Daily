<?php declare(strict_types = 1);


use App\Model\Repository\FranchiseRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\CharacterRepository;
use App\Model\Repository\ICharacterRepository;
/*
use App\Model\Repository\UserRepository;
use App\Model\Entity\IUserRepository;
*/

return [
    \PDO::class => function() {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_CHARSET']);
        
        return new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ]);
    },
    IFranchiseRepository::class => \DI\autowire(FranchiseRepository::class),
    ICharacterRepository::class => \DI\autowire(CharacterRepository::class),
    //IUserRepository::class => \DI\autowire(UserRepository::class)
];