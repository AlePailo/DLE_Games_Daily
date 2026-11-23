<?php declare(strict_types = 1);

use App\Core\ContainerFactory;
use App\Console\GenerateDailyChallengesCommand;
use App\Core\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
define('BASE_PATH', __DIR__ . '/../');

$dotenv = Environment::load(BASE_PATH);

$container = ContainerFactory::build();
$command = $container->get(GenerateDailyChallengesCommand::class);
$days = max(1, (int)(getopt('', ['days::'])['days'] ?? 1));
$command->execute($days);