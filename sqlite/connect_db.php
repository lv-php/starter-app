s<?php
//Init doctrine connection
$config = new \Doctrine\DBAL\Configuration();
//sqlite3 connection info
$connectionParams = array(
    'path' => __DIR__ . '/lvphp.sq3',
    'driver' => 'pdo_sqlite',
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);