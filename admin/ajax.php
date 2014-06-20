<?php
/**
 *  Common AJAX functions
 *  @author     Lee Garner <lee@leegarner.com>
 *  @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
 *  @package    locator
 *  @version    1.0.1
 *  @license    http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 *  @filesource
 */

/**
 *  Include required glFusion common functions
 */
require_once '../../../lib-common.php';

// This is for administrators only
if (!SEC_hasRights('locator.admin')) {
    $display .= COM_siteHeader('menu', $MESSAGE[30])
             . COM_showMessageText($MESSAGE[34], $MESSAGE[30])
             . COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to illegally access the locator administration screen.");
    echo $display;
    exit;
}

$base_url = $_CONF['site_url'];

switch ($_GET['action']) {
case 'toggleEnabled':
    $newval = $_REQUEST['newval'] == 1 ? 1 : 0;

    switch ($_GET['type']) {
    case 'is_origin':
        // Toggle the is_origin flag between 0 and 1
        DB_query("UPDATE {$_TABLES['locator_markers']}
                SET is_origin=$newval
                WHERE id='" . DB_escapeString($_REQUEST['id']). "'");
        break;

    case 'enabled':
        // Toggle the marker on or off for searching
        DB_query("UPDATE {$_TABLES['locator_markers']}
                SET enabled=$newval
                WHERE id='" . DB_escapeString($_REQUEST['id']). "'");
        break;
     default:
        exit;
    }

    header('Content-Type: text/xml');
    header("Cache-Control: no-cache, must-revalidate");
    //A date in the past
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <info>'. "\n";
    echo "<newval>$newval</newval>\n";
    echo "<id>{$_REQUEST['id']}</id>\n";
    echo "<type>{$_REQUEST['type']}</type>\n";
    echo "<baseurl>{$base_url}</baseurl>\n";
    echo "</info>\n";
    break;

default:
    exit;
}

?>
