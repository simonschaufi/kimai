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

/**
 * Perform the installation by creating all necessary tables
 * and some basic entries.
 */

/**
 * Execute an sql query in the database. The correct database connection
 * will be chosen and the query will be logged with the success status.
 *
 * @param string $query string query to execute
 */
function exec_query($query) {
    global $errors;
    $database = Kimai_Registry::getDatabase();

    $conn = $database->getConnectionHandler();
    $success = $conn->Query($query);

    //Kimai_Logger::logfile($query);
    if (!$success) {
        $errorInfo = serialize($conn->Error());
        Kimai_Logger::logfile('[ERROR] in [' . $query . '] => ' . $errorInfo);
        $errors = true;
    }
}

function quoteForSql($input)
{
    $database = Kimai_Registry::getDatabase();
    $conn = $database->getConnectionHandler();
    return "'" . $conn->SQLFix($input) . "'";
}

if (!isset($_REQUEST['accept'])) {
    header("Location: ../index.php?disagreedGPL=1");
    exit;
}

include('../includes/basics.php');

date_default_timezone_set($_REQUEST['timezone']);

$randomAdminID = random_number(9);

Kimai_Logger::logfile("-- begin install ----------------------------------");

// if any of the queries fails, this will be true
$errors = false;

$p = $kga['server_prefix'];

$query = "CREATE TABLE `${p}users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(160) NOT NULL,
  `alias` varchar(160),
  `trash` tinyint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `mail` varchar(160) DEFAULT NULL,
  `password` varchar(254) NULL DEFAULT NULL,
  `password_reset_hash` char(32) NULL DEFAULT NULL,
  `ban` int(1) NOT NULL default '0',
  `banTime` int(10) NOT NULL default '0',
  `secure` varchar(60) NOT NULL default '0',
  `last_project` int(10) NOT NULL default '1',
  `last_activity` int(10) NOT NULL default '1',
  `last_record` int(10) NOT NULL default '0',
  `timeframe_begin` varchar(60) NOT NULL default '0',
  `timeframe_end` varchar(60) NOT NULL default '0',
  `apikey` varchar(30) NULL DEFAULT NULL,
  `global_role_id` int(10) NOT NULL,
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `apikey` (`apikey`)
);";
exec_query($query);

$query = "CREATE TABLE `${p}preferences` (
  `user_id` int(10) NOT NULL,
  `option` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`option`)
);";
exec_query($query);

$query = "CREATE TABLE `${p}activities` (
  `activity_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `comment` TEXT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT '1',
  `filter` TINYINT(1) NOT NULL DEFAULT '0',
  `trash` TINYINT(1) NOT NULL DEFAULT '0'
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}groups` (
  `group_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(160) NOT NULL,
  `trash` TINYINT(1) NOT NULL DEFAULT '0'
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}groups_users` (
  `group_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `membership_role_id` int(10) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) AUTO_INCREMENT=1;";
exec_query($query);

// group/customer cross-table (groups n:m customers)
$query = "CREATE TABLE `${p}groups_customers` (
  `group_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  UNIQUE (`group_id` ,`customer_id`));";
exec_query($query);

// group/project cross-table (groups n:m projects)
$query = "CREATE TABLE `${p}groups_projects` (
  `group_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  UNIQUE (`group_id` ,`project_id`));";
exec_query($query);

// group/event cross-table (groups n:m events)
$query = "CREATE TABLE `${p}groups_activities` (
  `group_id` INT NOT NULL,
  `activity_id` INT NOT NULL,
  UNIQUE (`group_id` ,`activity_id`));";
exec_query($query);

// project/event cross-table (projects n:m events)
$query = "CREATE TABLE `${p}projects_activities` (
  `project_id` INT NOT NULL,
  `activity_id` INT NOT NULL,
  `budget` DECIMAL( 10, 2 ) NULL DEFAULT '0.00',
  `effort` DECIMAL( 10, 2 ) NULL ,
  `approved` DECIMAL( 10, 2 ) NULL,
  UNIQUE (`project_id`, `activity_id`));";
exec_query($query);

$query = "CREATE TABLE `${p}customers` (
  `customer_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `password` varchar(255),
  `password_reset_hash` char(32) NULL DEFAULT NULL,
  `secure` varchar(60) NOT NULL default '0',
  `comment` TEXT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT '1',
  `filter` TINYINT(1) NOT NULL DEFAULT '0',
  `company` varchar(255) NULL,
  `vat` varchar(255) NULL,
  `contact` varchar(255) NULL,
  `street` varchar(255) NULL,
  `zipcode` varchar(255) NULL,
  `city` varchar(255) NULL,
  `country` varchar(2) NULL,
  `phone` varchar(255) NULL,
  `fax` varchar(255) NULL,
  `mobile` varchar(255) NULL,
  `mail` varchar(255) NULL,
  `homepage` varchar(255) NULL,
  `timezone` varchar(255) NOT NULL,
  `trash` TINYINT(1) NOT NULL DEFAULT '0'
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}projects` (
  `project_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `customer_id` int(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` TEXT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT '1',
  `filter` TINYINT(1) NOT NULL DEFAULT '0',
  `trash` TINYINT(1) NOT NULL DEFAULT '0',
  `budget` decimal(10,2) NULL DEFAULT '0.00',
  `effort` DECIMAL( 10, 2 ) NULL,
  `approved` DECIMAL( 10, 2 ) NULL,
  `internal` TINYINT( 1 ) NOT NULL DEFAULT 0,
  INDEX ( `customer_id` )
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}time_sheet` (
  `time_entry_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `start` int(10) NOT NULL default '0',
  `end` int(10) NOT NULL default '0',
  `duration` int(6) NOT NULL default '0',
  `user_id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `activity_id` int(10) NOT NULL,
  `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `comment_type` TINYINT(1) NOT NULL DEFAULT '0',
  `cleared` TINYINT(1) NOT NULL DEFAULT '0',
  `location` VARCHAR(50),
  `tracking_number` varchar(30),
  `rate` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0',
  `fixed_rate` DECIMAL( 10, 2 ) DEFAULT NULL,
  `budget` DECIMAL( 10, 2 ) NULL,
  `approved` DECIMAL( 10, 2 ) NULL,
  `status_id` SMALLINT NOT NULL,
  `billable` TINYINT NULL,
  INDEX ( `user_id` ),
  INDEX ( `project_id` ),
  INDEX ( `activity_id` )
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}configuration` (
  `option` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`option`)
);";
exec_query($query);

$query = "CREATE TABLE `${p}rates` (
  `user_id` int(10) DEFAULT NULL,
  `project_id` int(10) DEFAULT NULL,
  `activity_id` int(10) DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL,
  UNIQUE KEY(`user_id`, `project_id`, `activity_id`)
);";
exec_query($query);

$query = "CREATE TABLE `${p}fixedRates` (
  `project_id` int(10) DEFAULT NULL,
  `activity_id` int(10) DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL,
  UNIQUE KEY(`project_id`, `activity_id`)
);";
exec_query($query);

$query = "CREATE TABLE `${p}expenses` (
  `expenseID` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `timestamp` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL,
  `project_id` int(10) NOT NULL,
  `designation` text NOT NULL,
  `comment` text NULL,
  `comment_type` tinyint(1) NOT NULL DEFAULT '0',
  `refundable` tinyint(1) unsigned NOT NULL default '0',
  `cleared` tinyint(1) NOT NULL DEFAULT '0',
  `multiplier` decimal(10,2) NOT NULL DEFAULT '1.00',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  INDEX ( `user_id` ),
  INDEX ( `project_id` )
) AUTO_INCREMENT=1;";
exec_query($query);

$query = "CREATE TABLE `${p}statuses` (
`status_id` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`status` VARCHAR( 200 ) NOT NULL
) ENGINE = InnoDB";
exec_query($query);

// The included script only sets up the initial permissions.
// Permissions that were later added follow below.
require("installPermissions.php");

foreach (array('customer', 'project', 'activity', 'group', 'user') as $object) {
    exec_query("ALTER TABLE `${p}global_roles` ADD `core-$object-otherGroup-view` tinyint DEFAULT 0;");
    exec_query("UPDATE `${p}global_roles` SET `core-$object-otherGroup-view` = 1 WHERE `name` = 'Admin';");
}

exec_query("INSERT INTO `${p}statuses` (`status_id` ,`status`) VALUES ('1', 'open'), ('2', 'review'), ('3', 'closed');");

// GROUPS
$defaultGroup = $kga['lang']['defaultGroup'];
$query = "INSERT INTO `${p}groups` (`name`) VALUES ('admin');";
exec_query($query);

// MISC
$query = "INSERT INTO `${p}activities` (`activity_id`, `name`, `comment`) VALUES (1, '" . $kga['lang']['testActivity'] . "', '');";
exec_query($query);

$query = "INSERT INTO `${p}customers` (`customer_id`, `name`, `comment`, `company`, `vat`, `contact`, `street`, `zipcode`, `city`, `phone`, `fax`, `mobile`, `mail`, `homepage`, `timezone`) VALUES (1, '" . $kga['lang']['testCustomer'] . "', '', '', '', '', '', '', '', '', '', '', '',''," . quoteForSql($_REQUEST['timezone']) . ");";
exec_query($query);

$query = "INSERT INTO `${p}projects` (`project_id`, `customer_id`, `name`, `comment`) VALUES (1, 1, '" . $kga['lang']['testProject'] . "', '');";
exec_query($query);


// ADMIN USER
$adminPassword = encode_password('changeme');
$query = "INSERT INTO `${p}users` (`user_id`, `name`, `mail`, `password`, `globalRoleID` ) VALUES ('$randomAdminID','admin','admin@example.com','$adminPassword',1);";
exec_query($query);

$query = "INSERT INTO `${p}preferences` (`user_id`,`option`,`value`) VALUES
('$randomAdminID', 'ui.rowlimit', '100'),
('$randomAdminID', 'ui.skin', 'standard'),
('$randomAdminID', 'ui.showCommentsByDefault', '0'),
('$randomAdminID', 'ui.hideOverlapLines', '1'),
('$randomAdminID', 'ui.showTrackingNumber', '1'),
('$randomAdminID', 'ui.showBillability', '0'),
('$randomAdminID', 'ui.inlineEditingOfDescriptions', '0'),
('$randomAdminID', 'timezone', " . quoteForSql($_REQUEST['timezone']) . ");";
exec_query($query);

// Configuration
exec_query("INSERT INTO `${p}configuration` (`option`, `value`) VALUES
('version', '" . $kga['version'] . "'),
('login', '1'),
('adminmail', 'admin@example.com'),
('loginTries', '3'),
('loginBanTime', '900'),
('revision', '" . $kga['revision'] . "'),
('currency_name', 'Euro'),
('currency_sign', '€'),
('currency_first', '0'),
('show_update_warn', '1'),
('check_at_startup', '0'),
('show_daySeperatorLines', '1'),
('show_gabBreaks', '0'),
('show_RecordAgain', '1'),
('show_TrackingNr', '1'),
('date_format_0', 'dd.mm.yy'),
('date_format_1', '%d.%m.'),
('date_format_2', '%d.%m.%Y'),
('date_format_3', 'd.m.Y'),
('table_time_format', '%H:%M'),
('language', '" . $kga['language'] . "'),
('roundPrecision', '0'),
('decimalSeparator', ','),
('durationWithSeconds', '0'),
('exactSums', '0'),
('defaultVat', '0'),
('editLimit', '0'),
('roundTimesheetEntries', '0'),
('roundMinutes', '0'),
('roundSeconds', '0'),
('allowRoundDown', '0'),
('defaultStatusID', '1')
");

// CROSS TABLES
$query = "INSERT INTO `${p}groups_users` (`group_id`, `user_id`, `membership_role_id`) VALUES (1, '" . $randomAdminID . "', 1);";
exec_query($query);

$query = "INSERT INTO `${p}groups_activities` (`group_id`, `activity_id`) VALUES (1, 1);";
exec_query($query);

$query = "INSERT INTO `${p}groups_customers` (`group_id`, `customer_id`) VALUES (1, 1);";
exec_query($query);

$query = "INSERT INTO `${p}groups_projects` (`group_id`, `project_id`) VALUES (1, 1);";
exec_query($query);

if ($errors) {
    $view = new Zend_View();
    $view->setBasePath(WEBROOT . '/templates');

    $view->assign('headline', $kga['lang']['errors'][1]['hdl']);
    $view->assign('message', $kga['lang']['errors'][1]['txt']);
    echo $view->render('misc/error.php');
    Kimai_Logger::logfile("-- showing install error --------------------------");
} else {
    Kimai_Logger::logfile("-- installation finished without error ------------");
    header("Location: ../index.php");
}
