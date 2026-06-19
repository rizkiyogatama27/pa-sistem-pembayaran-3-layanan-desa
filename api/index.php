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
$_SERVER['APP_DEBUG'] = 'false';
$_SERVER['LOG_CHANNEL'] = 'stderr';
putenv('APP_DEBUG=false');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=/tmp');
putenv('SESSION_DRIVER=cookie');

require __DIR__ . '/../public/index.php';
