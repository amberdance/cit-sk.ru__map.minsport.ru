<?php

/**
 * @param string $mode
 *
 * @return void
 */
function setEnvironmentMode(string $mode): void
{
    if (is_numeric(strpos($mode, "prod"))) {
        define('API_URL', 'https://glossary.stavregion.ru');
        define('DB_DEBUG', false);
    }

    if (is_numeric(strpos($mode, "dev"))) {
        define('API_URL', 'http://sport-slots');
        define('DB_DEBUG', true);
    }
}

define('DS', DIRECTORY_SEPARATOR);

define('ROOT', dirname(dirname(__FILE__)));

define("UPLOADS_DIR", "{$_SERVER['DOCUMENT_ROOT']}/uploads");

define('ROUTES', "{$_SERVER['DOCUMENT_ROOT']}/api/citsk/config/routes.php");

define('SERVICE_MANAGEMENT_PREFIX', 'GF');

define("BLACKLIST", [".php", ".phtml", ".php3", ".php4", ".html", ".htm", ".js", ".txt"]);

define("ALLOWED_MIME", ['image/png', 'image/jpeg']);

define('JWT', [
    'iss'        => 'https://glossary.stavregion.ru',
    'aud'        => 'https://glossary.stavregion.ru',
    'iat'        => 1590008094,
    'nbf'        => 1590008094,
    'secret_key' => "|mIIGLfLZ*fGiYXlb|^PO=ZTs;SHXngSW()&dtW3:rz_vB;vb]nTP49-NbH0`0",
]);
