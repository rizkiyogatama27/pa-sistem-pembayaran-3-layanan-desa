<?php
// Forward Vercel requests to normal index.php
// Cache-busting: setiap kali file ini diubah, cache lama di /tmp otomatis tidak dipakai
$_cacheVersion = md5(filemtime(__FILE__));
$_tmpDir = '/tmp/v_' . $_cacheVersion;

$_SERVER['APP_CONFIG_CACHE']   = $_tmpDir . '/config.php';
$_SERVER['APP_EVENTS_CACHE']   = $_tmpDir . '/events.php';
$_SERVER['APP_PACKAGES_CACHE'] = $_tmpDir . '/packages.php';
$_SERVER['APP_ROUTES_CACHE']   = $_tmpDir . '/routes.php';
$_SERVER['APP_SERVICES_CACHE'] = $_tmpDir . '/services.php';
$_SERVER['VIEW_COMPILED_PATH'] = $_tmpDir;
$_SERVER['SESSION_DRIVER']     = 'cookie';
$_SERVER['CACHE_PREFIX']       = 'vercel_';
$_SERVER['APP_DEBUG']          = 'false';
$_SERVER['LOG_CHANNEL']        = 'stderr';
putenv('APP_DEBUG=false');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=' . $_tmpDir);
putenv('SESSION_DRIVER=cookie');

require __DIR__ . '/../public/index.php';
