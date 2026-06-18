<?php
// Forward Vercel requests to normal index.php
$_SERVER['APP_CONFIG_CACHE'] = '/tmp/config.php';
$_SERVER['APP_EVENTS_CACHE'] = '/tmp/events.php';
$_SERVER['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_SERVER['APP_ROUTES_CACHE'] = '/tmp/routes.php';
$_SERVER['APP_SERVICES_CACHE'] = '/tmp/services.php';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp';
$_SERVER['SESSION_DRIVER'] = 'cookie';
$_SERVER['CACHE_PREFIX'] = 'vercel_';

require __DIR__ . '/../public/index.php';
