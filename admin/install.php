<?php
/**
*   Default installation values for the Locator plugin for glFusion
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/

/** Include required common glFusion functions */
require_once('../../../lib-common.php');

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the locator install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_GEO['access_denied']);
    $display .= $LANG_GEO['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$base_path = "{$_CONF['path']}plugins/locator";
require_once $base_path . '/functions.inc';

// Default data
$DEFVALUES = array();

// Security Feature to add
$NEWFEATURE = array();
$NEWFEATURE['locator.view']  ="locator Access";
$NEWFEATURE['locator.submit']   ="locator Submission Rights";
$NEWFEATURE['locator.admin']  ="locator Admin Rights";


/**
*   Puts the datastructures for this plugin into the Geeklog database
*   Note: Corresponding uninstall routine is in functions.inc
*   @return   boolean True if successful False otherwise
*   @ignore
*/
function plugin_install_locator()
{
    global $NEWTABLE, $DEFVALUES, $NEWFEATURE, $_CONF_GEO;
    global $_TABLES, $_CONF, $_DB_dbms;

    $pi_name = $_CONF_GEO['pi_name'];
    $pi_version = $_CONF_GEO['pi_version'];
    $pi_url = $_CONF_GEO['pi_url'];
    $gl_version = $_CONF_GEO['gl_version'];

    COM_errorLog("Attempting to install the $pi_name Plugin",1);

    // Create the Plugins Tables
    require_once $_CONF['path'] . 'plugins/' . $_CONF_GEO['pi_name'] .
            '/sql/' . $_DB_dbms . '_install.php';
    for ($i = 1; $i <= count($_SQL); $i++) {
        $progress .= "executing " . current($_SQL) . "<br>\n";
        COM_errorLOG("executing " . current($_SQL));
        DB_query(current($_SQL),'1');
        if (DB_error()) {
            COM_errorLog("Error Creating $table table",1);
            plugin_uninstall_locator('DeletePlugin');
            return false;
            exit;
        }
        next($_SQL);
    }
    COM_errorLog("Success - Created $table table",1);

    // Insert Default Data
    foreach ($DEFVALUES as $table => $sql) {
        COM_errorLog("Inserting default data into $table table",1);
        DB_query($sql,1);
        if (DB_error()) {
            COM_errorLog("Error inserting default data into $table table",1);
            plugin_uninstall_locator();
            return false;
            exit;
        }
        COM_errorLog("Success - inserting data into $table table",1);
    }

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO 
            {$_TABLES['groups']} 
            (grp_name, grp_descr) 
        VALUES 
            ('$pi_name Admin', 
            'Users in this group can administer the $pi_name plugin')",
    1);
    if (DB_error()) {
        plugin_uninstall_locator();
        return false;
        exit;
    }
    COM_errorLog('...success',1);
    $group_id = DB_insertId();

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO 
            {$_TABLES['vars']} 
        VALUES 
            ('{$pi_name}_gid', $group_id)",
    1);
    if (DB_error()) {
        plugin_uninstall_locator();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        COM_errorLog("Adding $feature feature",1);
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
            . "VALUES ('$feature','$desc')",1);
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature",1);
            plugin_uninstall_locator();
            return false;
            exit;
        }
        $feat_id = DB_insertId();
        COM_errorLog("Success",1);
        COM_errorLog("Adding $feature feature to admin group",1);
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature to admin group",1);
            plugin_uninstall_locator();
            return false;
            exit;
        }
        COM_errorLog("Success",1);
    }        

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    COM_errorLog("Attempting to give all users in Root group access to $pi_name admin group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        plugin_uninstall_locator();
        return false;
        exit;
    }

    // Load the online configuration records
    if (function_exists('plugin_load_configuration')) {
        if (!plugin_load_configuration($group_id)) {
            PLG_uninstall($pi_name);

            return false;
        }
    }

    // Register the plugin with glFusion
    COM_errorLog("Registering $pi_name plugin with glFusion", 1);
    DB_delete($_TABLES['plugins'],'pi_name',$pi_name);
    DB_query("INSERT INTO 
            {$_TABLES['plugins']} 
            (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) 
        VALUES 
            ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)"
    );
    if (DB_error()) {
        plugin_uninstall_locator();
        return false;
        exit;
    }

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}


/**
*   Load configuration items into the config system
*   @param  integer $group_id   Group ID to use as default (optional)
*   @return boolean             Result from plugin_initconfig_locator()
*/
function plugin_load_configuration($group_id=0)
{
    global $_CONF, $base_path;

    /** Include the glFusion configuration class */
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    /** Include the default configuration values */
    require_once $base_path . '/install_defaults.php';

    return plugin_initconfig_locator($group_id);
}


/* 
* Main Function
*/

if (SEC_checkToken()) {
    $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
    switch ($action) {
    case 'install':
        if (PLG_install($_CONF_GEO['pi_name'])) {
            $msg = '?msg=44';
        } else {
            $msg = '?msg=72';
        }
        break;

    case 'uninstall':
        if (PLG_uninstall($_CONF_GEO['pi_name'])) {
            $msg = '?msg=45';
        } else {
            $msg = '?msg=73';
        }
        break;
    }
}
echo COM_refresh("{$_CONF['site_admin_url']}/plugins.php$msg");

?>
