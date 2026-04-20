<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Migration;

$migration = new Migration();

if (isset($argv[1]) && $argv[1] === 'down') {
    $migration->down();
} elseif (isset($argv[1]) && $argv[1] === 'reset') {
    $migration->reset();
} else {
    $migration->up();
}
