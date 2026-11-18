<?php declare(strict_types = 1);

use App\Core\ContainerFactory;
use App\Console\RebuildUserStatsCommand;

require_once __DIR__ . '/../vendor/autoload.php';
define('BASE_PATH', __DIR__ . '/../');

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

$container = ContainerFactory::build();
$command = $container->get(RebuildUserStatsCommand::class);
$command->execute(in_array('--apply', $argv, true));