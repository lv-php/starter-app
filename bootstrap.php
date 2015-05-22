<?php

// The default timezone should always be set when using dates and times
date_default_timezone_set('America/Los_Angeles');

// Are we in development mode?
$isDevMode = true;

if (!$isDevMode) {
    // Turn off error reporting to hide it from the users.  Hiding errors from users prevents exposing the internal
    // workings of your application and the possibility of displaying secret or private data in the error.
    error_reporting(0);
}

require_once 'vendor/autoload.php';
require_once 'sqlite/connect_db.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use DMS\Service\Meetup\MeetupKeyAuthClient;

// Path to entities
$paths = array(__DIR__ . '/src/LVPHP/Models');

if ($isDevMode) {
    $cache = new \Doctrine\Common\Cache\ArrayCache;
} else {
    $cache = new \Doctrine\Common\Cache\ApcCache;
}

$config = Setup::createConfiguration($isDevMode);
$driver = new AnnotationDriver(new AnnotationReader(), $paths);
AnnotationRegistry::registerLoader('class_exists');
$config->setMetadataDriverImpl($driver);
// Cache
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);
// Proxies
$config->setProxyDir(__DIR__.'/src/LVPHP/Proxies');
$config->setProxyNamespace('LVPHP\Proxies');
if ($isDevMode) {
    $config->setAutoGenerateProxyClasses(true);
} else {
    $config->setAutoGenerateProxyClasses(false);
}

// Obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

// reCaptcha key
if (file_exists(__DIR__ . '/config/recaptcha.prod.php')) {
    $recaptchaApiKey = include_once __DIR__ . '/config/recaptcha.prod.php';
} else {
    $recaptchaApiKey = include_once __DIR__ . '/config/recaptcha.php';
}

// Meetup.com Key Authentication
if (file_exists(__DIR__ . '/config/meetup-api.prod.php')) {
    $meetupApiKey = include_once __DIR__ . '/config/meetup-api.prod.php';
} else {
    $meetupApiKey = include_once __DIR__ . '/config/meetup-api.php';
}
$meetupClient = MeetupKeyAuthClient::factory(array('key' => $meetupApiKey));