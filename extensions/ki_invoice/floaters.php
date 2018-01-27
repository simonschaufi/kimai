<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) 2006-2009 Kimai-Development-Team
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

// Set up the application for the extension
call_user_func(function () {
    $classLoader = require __DIR__ . '/../../libraries/autoload.php';
    (new Kimai_Extension_Application($classLoader))->run();
});

$isCoreProcessor = 0;
$dir_templates = 'templates/';
require('../../includes/kspi.php');

switch ($axAction) {
    case 'editVat':
        if (isset($kga['customer'])) {
            die();
        }
        echo $view->render("floaters/editVat.php");
        break;
}
