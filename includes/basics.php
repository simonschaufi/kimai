<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) Kimai-Development-Team since 2006
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Basic initialization takes place here.
 * From loading the configuration to connecting to the database this all is done
 * here.
 */

defined('WEBROOT') || define('WEBROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            '.',
            realpath(APPLICATION_PATH . '/libraries/'),
            realpath(APPLICATION_PATH . '/libraries/zendframework/zendframework1/library/')
        )
    )
);

ini_set('display_errors', '1');

require_once WEBROOT . '/libraries/autoload.php';
require_once WEBROOT . '/includes/func.php';

$kga = new Kimai_Config(array(
    'server_prefix' => $GLOBALS['KIMAI_CONF_VARS']['DB']['prefix'],
    'authenticator' => $GLOBALS['KIMAI_CONF_VARS']['SYS']['authenticator'],
    'language' => $GLOBALS['KIMAI_CONF_VARS']['UI']['language'],
    'skin' => $GLOBALS['KIMAI_CONF_VARS']['UI']['skin']
));

// write vars from autoconf.php into kga
if (isset($billable)) {
    $kga->setBillable($billable);
}

// will inject the version variables into the Kimai_Config object
include WEBROOT . 'includes/version.php';

Kimai_Registry::setConfig($kga);

// ============ global namespace cleanup ============
// remove some variables from the global namespace, that should either be
// not accessible or which are available through the kga config object
foreach (array('billable') as $varName) {
    if (isset($$varName)) {
        unset($$varName);
    }
}

// ============ setup database ============
// we do not unset the $database variable
// as it is historically referenced in many places from the global namespace
$database = new Kimai_Database_Mysql($GLOBALS['KIMAI_CONF_VARS']['DB'], true);
if (!$database->isConnected()) {
    die('Kimai could not connect to database. Check your autoconf.php.');
}
Kimai_Registry::setDatabase($database);

// ============ setup authenticator ============
$authClass = 'Kimai_Auth_' . ucfirst($kga->getAuthenticator());
if (!class_exists($authClass)) {
    $authClass = 'Kimai_Auth_Kimai';
}
$authPlugin = new $authClass($database, $kga);
Kimai_Registry::setAuthenticator($authPlugin);
unset($authPlugin);

// ============ load global configurations ============
$database->initializeConfig($kga);

// ============ setup translation object ============
Kimai_Registry::setTranslation(
    (new Kimai_Translation_Service())->load(
        $kga->getLanguage()
    )
);

$tmpDir = WEBROOT . '/temporary/';
if (!file_exists($tmpDir) || !is_dir($tmpDir) || !is_writable($tmpDir)) {
    die('Kimai needs write permissions for: temporary/');
}

$frontendOptions = array('lifetime' => 7200, 'automatic_serialization' => true);
$backendOptions = array('cache_dir' => $tmpDir);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
Kimai_Registry::setCache($cache);
Zend_Locale::setCache($cache);
