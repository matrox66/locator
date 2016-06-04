<?php
/**
*   Upgrade the plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

global $_CONF, $_CONF_GEO, $_CONF_GEO, $_DB_dbms;

/** Include default values for new config items */
require_once "{$_CONF['path']}plugins/{$_CONF_GEO['pi_name']}/install_defaults.php";

/**
*   Sequentially perform version upgrades.
*   @param current_ver string Existing installed version to be upgraded
*   @return integer Error code, 0 for success
*/
function locator_do_upgrade($current_ver)
{
    $error = 0;

    if ($current_ver < '0.1.1') {
        $error = locator_upgrade_0_1_1();
        if ($error)
            return $error;
    }

    if ($current_ver < '0.1.4') {
        $error = locator_upgrade_0_1_4();
        if ($error)
            return $error;
    }

    if ($current_ver < '1.0.1') {
        $error = locator_upgrade_1_0_1();
        if ($error)
            return $error;
    }

    if ($current_ver < '1.0.2') {
        $error = locator_upgrade_1_0_2();
        if ($error)
            return $error;
    }

    if ($current_ver < '1.1.0') {
        $error = locator_upgrade_1_1_0();
        if ($error)
            return $error;
    }

    return $error;

}


/**
*   Execute the SQL statement to perform a version upgrade.
*   An empty SQL parameter will return success.
*
*   @param string   $version  Version being upgraded to
*   @param array    $sql      SQL statement to execute
*   @return integer Zero on success, One on failure.
*/
function locator_do_upgrade_sql($version='Undefined', $sql='')
{
    global $_TABLES, $_CONF_GEO;

    // We control this, so it shouldn't happen, but just to be safe...
    if ($version == 'Undefined') {
        COM_errorLog("Error updating {$_CONF_GEO['pi_name']} - Undefined Version");
        return 1;
    }

    // If no sql statements passed in, return success
    if (!is_array($sql))
        return 0;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating Geo Locator to version $version");
    for ($i = 1; $i <= count($sql); $i++) {
        COM_errorLOG("Locator Plugin $version update: Executing SQL => " . current($sql));
        DB_query(current($sql),'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Locator plugin update",1);
            return 1;
            break;
        }
        next($sql);
    }

    return 0;

}


/**
*   Upgrade to version 0.1.1
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_0_1_1()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('show_right_blk', $_GEO_DEFAULT['show_right_blk'], 
               'select', 0, 0, 3, 70, true, $_CONF_GEO['pi_name']);
    }

    return locator_do_upgrade_sql('0.1.1', $sql);

}

/**
*   Upgrade to version 0.1.4
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_0_1_4()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('purge_userlocs', $_GEO_DEFAULT['purge_userlocs'], 
               'text', 0, 0, 3, 70, true, $_CONF_GEO['pi_name']);
    }

    $sql[] = "ALTER TABLE {$_TABLES['locator_userloc']}
        ADD type TINYINT(1) DEFAULT 0 AFTER id";
    $sql[] = "ALTER TABLE {$_TABLES['locator_userloc']}
        CHANGE add_date add_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"; 
    // new "enabled" field for markers
    $sql[] = "ALTER TABLE {$_TABLES['locator_markers']}
        ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";

    return locator_do_upgrade_sql('0.1.4', $sql);

}

/**
*   Upgrade to version 1.0.1
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_0_1()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        // Remove individual block selections and combine into one.
        // Assume left blocks are shown, since it wasn't an option before.
        $displayblocks = 1;
        if ($_CONF_GEO['show_right_blk']) $displayblocks += 2;

        $c->del('show_right_blk', $_CONF_GEO['pi_name']);
        $c->add('displayblocks', $displayblocks,
                'select', 0, 0, 13, 70, true, $_CONF_GEO['pi_name']);

        $c->add('profile_showmap', $_GEO_DEFAULT['profile_showmap'], 
               'select', 0, 0, 3, 80, true, $_CONF_GEO['pi_name']);

        $c->add('usermenu_option', $_GEO_DEFAULT['usermenu_option'], 
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);

    }

    // Add 'enabled' field to submissions that should have been in 0.1.4
    $sql[] = "ALTER TABLE {$_TABLES['locator_submission']}
        ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";

    return locator_do_upgrade_sql('1.0.1', $sql);

}


/**
*   Upgrade to version 1.0.2
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_0_2()
{
    global $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('use_weather', $_GEO_DEFAULT['use_weather'], 
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);
        $c->add('use_directions', $_GEO_DEFAULT['use_directions'], 
               'select', 0, 0, 3, 100, true, $_CONF_GEO['pi_name']);
    }

    return 0;
}


/**
*   Upgrade to version 1.1.0
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_1_0()
{
    global $_CONF_GEO;

    // Remove deprecated configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->del('url_geocode', $_CONF_GEO['pi_name']);
    }

    return 0;
}

?>
