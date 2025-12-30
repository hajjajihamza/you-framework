<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/tests/Fixtures/Entity/User.php'; // Manually require since we don't have composer autoload for tests

use YouOrm\Discovery\EntityDiscovery;
use YouOrm\Grammar\DDL\MySqlGrammarDDL;
use YouOrm\Migration\MigrationGenerator;

// Setup
$grammar = new MySqlGrammarDDL();
$discovery = new EntityDiscovery();
$generator = new MigrationGenerator($grammar, $discovery);

// Path to scan
$path = __DIR__ . '/tests/Fixtures/Entity';

// Generate
echo "Scanning path: $path\n";
$sql = $generator->generate([$path]);

echo "Generated SQL:\n";
echo $sql . "\n";
