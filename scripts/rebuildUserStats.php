<?php declare(strict_types = 1);

use App\Core\ContainerFactory;
use App\Console\RebuildUserStatsCommand;
use App\Core\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
define('BASE_PATH', __DIR__ . '/../');

$dotenv = Environment::load(BASE_PATH);

$container = ContainerFactory::build();
$command = $container->get(RebuildUserStatsCommand::class);
$command->execute(in_array('--apply', $argv, true));