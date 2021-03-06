<?php
/**
*   glFusion API functions for the Locator plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.2.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

/**
*   Global plugin-specific configuration items
*   @global array $_CONF_GEO
*/
global $_CONF_GEO;

//  Check and see if we need to load the plugin configuration
if (!isset($_CONF_GEO['default_radius'])) {
    /** Include the glFusion configuration class if not done already */
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $geo_config = config::get_instance();
    $_CONF_GEO = $geo_config->get_config('locator');
}
$_CONF_GEO['_is_uikit'] = $_SYSTEM['framework'] == 'uikit' ? true : false;

define('LOCATOR_PI_PATH', __DIR__);
define('LOCATOR_URL', $_CONF['site_url'] . '/locator');
define('LOCATOR_ADMIN_URL', $_CONF['site_admin_url'] . '/plugins/locator');

require_once LOCATOR_PI_PATH . '/locator.php';

/** Include local configuration overrides */
if (file_exists(LOCATOR_PI_PATH . '/config.php')) {
    include_once LOCATOR_PI_PATH . '/config.php';
}


/**
* Language file Include
*/
$langpath = LOCATOR_PI_PATH . '/language';
$langfile = "{$_CONF['language']}.php";
if (!file_exists("$langpath/$langfile")) {
    $langfile = 'english.php';
}
include_once "$langpath/$langfile";
if (file_exists("$langpath/custom/$langfile")) {
    include_once "$langpath/custom/$langfile";
}

spl_autoload_register(function ($class)
{
    // project-specific namespace prefix
    $prefix = 'Locator\\';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/classes/';

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


function USES_locator_functions()
{   require_once LOCATOR_PI_PATH . '/locator_functions.inc.php';    }

function USES_locator_editmarker()
{   require_once LOCATOR_PI_PATH . '/editmarker.php';   }

function USES_locator_google_lang()
{   require_once LOCATOR_PI_PATH . '/google_lang.inc.php';  }


/**
*   Returns the items for this plugin that should appear on the main menu
*
*   @return array   Associative array of menu items to be added to menu
*/
function plugin_getmenuitems_locator()
{
    global $LANG_GEO, $_CONF_GEO;

    $menuitems = '';
    if ($_CONF_GEO['usermenu_option'] == 1 &&
            SEC_hasRights($_CONF_GEO['pi_name'].'.view')) {
        $menuitems = array(
            $LANG_GEO['menulabel'] => LOCATOR_URL . '/index.php',
        );
    }

    return $menuitems;
}


/**
*   Add the admin option to command & control.
*
*   @return   array   Array (plugin name, admin url, url of plugin icon)
*/
function plugin_cclabel_locator()
{
    global $LANG_GEO;

    if (plugin_ismoderator_locator()) {
        return array(
            $LANG_GEO['plugin_name'],
            LOCATOR_ADMIN_URL . '/index.php',
            plugin_geticon_locator(),
        );
    } else {
        return false;
    }
}


/**
*   Returns the administrative menu option for this plugin.
*
*   @return array   Array (plugin name, admin url, # of plugin items or '')
*/
function plugin_getadminoption_locator()
{
    global $_CONF, $LANG_GEO, $_CONF_GEO;

    if (plugin_ismoderator_locator()) {
        return array(
            $LANG_GEO['admin_menu'],
            LOCATOR_ADMIN_URL . '/index.php',
            ''
        );
    } else {
        return false;
    }
}


/**
*   Returns the user menu option for this plugin.
*
*   @return array   Array containing (plugin name, plugin user url, 0)
*/
function plugin_getuseroption_locator()
{
    return false;
}


/**
*   Return the current version of code.
*   Used in the Plugin Editor to show the registered version and code version
*
*   @return string Plugin version
*/
function plugin_chkVersion_locator()
{
    global $_CONF_GEO;

    return $_CONF_GEO['pi_version'];
}


/**
*   Removes the datastructures for this plugin from the glFusion database.
*   This routine will get called from the Plugin install program if user
*   selects De-Install or if Delete is used in the Plugin Editor.
*   The Plugin Installer will also call this routine upon and install error
*   to remove anything it has created.
*   The Plugin installer will pass the optional parameter which will then
*   double check that plugin has first been disabled.
*
*   Returns True if all Plugin related data is removed without error
*
*   @param  string  $installCheck   Defult is blank but if set, check if plugin is disabled first
*   @return boolean                 True if successful false otherwise
*/
function plugin_uninstall_locator($installCheck='')
{
    global $_TABLES, $LANG_PHOTO;

    $pi_name='locator';
    $FEATURES = array (
        'locator.admin',
        'locator.view',
        'locator.submit',
    );
    $TABLES = array (
        'locator_markers',
        'locator_userXorigin',
        'locator_submission',
        'locator_userloc',
        'locator_cache',
    );

    // Ok to proceed and delete plugin

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the ' . $pi_name . ' plugin from glFusion',1);
    DB_delete($_TABLES['plugins'], 'pi_name', $pi_name);

    // Drop locator tables
    foreach($TABLES as $table) {
        COM_errorLog("Removing Table {$_TABLES[$table]}",1);
        DB_query("DROP TABLE {$_TABLES[$table]}",1);
    }

    // Remove Security for this plugin
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing elements of feature $feature");
        $grp_id = (int)DB_getItem($_TABLES['vars'], 'value', "name = 'locator_gid'");
        if ($grp_id > 0) {
            COM_errorLog("Removing $pi_name Admin Group", 1);
            DB_delete($_TABLES['groups'], 'grp_id', $grp_id);
            DB_delete($_TABLES['vars'], 'name', 'locator_gid');
            COM_errorLog("Removing root users from admin of $pi_name");
            DB_delete($_TABLES['group_assignments'], 'ug_main_grp_id', $grp_id);
        }
        // Remove all the associated features -- access rights
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = (int)DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        if ($feat_id > 0) {
            DB_delete($_TABLES['access'], 'acc_ft_id', $feat_id);
            DB_delete($_TABLES['features'], 'ft_id', $feat_id);
        }
    }
    if (function_exists('CACHE_clear')) {
        CACHE_clear('locator');
    }
    COM_errorLog('...success',1);
    return true;
}


/**
*   Gets an array of items to be removed during plugin removal.
*
*   @return array Array of plugin elements to remove
*/
function plugin_autouninstall_locator()
{
    global $_CONF_GEO;

    $out = array (
        'tables'    => array(
            $_CONF_GEO['pi_name']. '_markers',
            $_CONF_GEO['pi_name']. '_submission',
            $_CONF_GEO['pi_name']. '_userXorigin',
            $_CONF_GEO['pi_name']. '_userloc',
            $_CONF_GEO['pi_name']. '_cache',
        ),
        'groups'    => array(
            $_CONF_GEO['pi_name'] . ' Admin',
        ),
        'features'  => array(
            $_CONF_GEO['pi_name'] . '.admin',
            $_CONF_GEO['pi_name'] . '.submit',
            $_CONF_GEO['pi_name'] . '.view',
        ),
        'php_blocks' => array(),
        'vars'      => array(
            $_CONF_GEO['pi_name'] . '_gid',
        ),
    );
    if (function_exists('CACHE_clear')) {
        CACHE_clear('locator');
    }
    \Locator\Cache::clear();
    return $out;
}


/**
*   Returns the current user's ability to moderate the plugin
*
*   Checks that the current user has the rights to moderate the
*   plugin, returns true if this is the case, false otherwise
*
*   @return boolean True if moderator
*/
function plugin_ismoderator_locator()
{
    global $_CONF_GEO;
    static $isMod = NULL;
    if ($isMod === NULL) {
        $isMod = SEC_hasRights($_CONF_GEO['pi_name'] . '.admin');
    }
    return $isMod;
}



/**
*   Returns the plugins submission form
*   Returns a string contain the HTML of the submission form for the plugin.
*
*   @return string  The HTML form
*/
function plugin_submit_locator()
{
    global $HTTP_POST_VARS, $LANG12, $_CONF, $_CONF_GEO, $LANG_GEO;

    // only valid users allowed
    if (!GEO_canSubmit()) {
        return GEO_errorMsg($LANG_GEO['access_denied_msg1'], 'alert',
                $LANG_GEO['access_denied_msg']);
    }

    // If mode = preview or editsubmission, load the ad info.
    if ($HTTP_POST_VARS['mode'] == $LANG12[32] ||
        $HTTP_POST_VARS['mode'] == 'editsubmission') {
        $A = $HTTP_POST_VARS;
    } else {
        $A = array();
    }

    $M = new Locator\Marker();
    return $M->Edit();
}


/**
*   Returns the URL of the plugin's icon.
*
*   @return string  URL of the icon
*/
function plugin_geticon_locator()
{
    return LOCATOR_URL . '/images/locator.png';
}


/**
*   Search the plugin data
*/
function plugin_dopluginsearch_locator(
    $query,
    $datestart,
    $dateend,
    $topic,
    $type,
    $author,
    $keyType,
    $page,
    $perpage)
{
    global $_CONF, $_TABLES, $LANG_GEO, $_CONF_GEO;
    if (COM_isAnonUser() && $_CONF['loginrequired'] == 1) {
        return '';
    }

    // Encode query for inclusion in result's url
    $htmlquery = urlencode($query);

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    // Start the SQL query by selecting desired fields.
    $sql = "SELECT
            a.id as id, a.title as title,
            a.description as description, a.owner_id, a.views as hits,
            a.add_date AS date,
            CONCAT('/{$_CONF_GEO['pi_name']}/index.php?detail=x&id=',a.id) as url
        FROM {$_TABLES['locator_markers']} a
        WHERE 1=1 " . COM_getPermSQL('AND', 0, 2, 'a'). ' ';

    if (!empty($author)) {
        $sql .= " AND (a.owner_id = '$author') ";
    }

    $search = new SearchCriteria($_CONF_GEO['pi_name'], $LANG_GEO['menulabel']);
    $columns = array('a.title', 'a.description', 'a.address',
        'a.city', 'a.state', 'a.postal', 'a.keywords', 'a.url');
    list($sql, $ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    //echo $sql;die;
    $search->setSQL($sql);
    //$search->setRank(5);
    //$search->setURLRewrite(true);

    return $search;

}


/**
*   Returns the plugin menu option for searching
*
*   @return array Plugin name => Menu Label
*/
function plugin_searchtypes_locator()
{
    global $LANG_GEO, $_CONF, $_CONF_GEO;

    if (!COM_isAnonUser() || $_CONF['loginrequired'] != 1) {
        return array($_CONF_GEO['pi_name'] => $LANG_GEO['menulabel']);
    } else {
        return '';
    }
}


/**
*   Returns the number of submissions waiting moderation
*   Calculates the number of submissions that are currently waiting
*   to be moderated and returns that number.
*
*   @return      int       number of submissions awaiting moderation
*/
function plugin_submissioncount_locator()
{
    global $_TABLES;

    return DB_count($_TABLES['locator_submission']);
}


/**
*   Show items for moderation on submissions page
*   Uses the Plugin class to return data required by moderation.php to list
*   plugin objects that need to be moderated.
*
*   @return       Plugin       return Plugin class containing submission data
*/
function plugin_itemlist_locator()
{
    global $_TABLES, $LANG_GEO;

    if (plugin_ismoderator_locator()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_GEO['menulabel'];
        $plugin->getsubmissionssql = "SELECT id, title, address, 'n/a', owner_id as uid FROM "
                                   . $_TABLES['locator_submission'];
        $plugin->addSubmissionHeading($LANG_GEO['title']);
        $plugin->addSubmissionHeading($LANG_GEO['address']);
        return $plugin;
    }
}


/**
*   Returns list of moderation values
*
*   The array returned contains (in order): the row 'id' label, main plugin
*   table, moderation fields (comma seperated), and plugin submission table
*
*   @return       array        Returns array of useful moderation values
*/
function plugin_moderationvalues_locator()
{
    global $_TABLES;

    return array(
        'id',
        $_TABLES['locator_markers'],
        'id, owner_id, title, address, city, state, postal,
            description, lat, lng, ' .
            'is_origin, keywords, url, add_date, group_id, ' .
            'perm_owner, perm_group, perm_members, perm_anon, enabled',
        $_TABLES['locator_submission']);
}


/**
*   Get plugin-specific Javascript into the header
*
*   @return array   Array of filenames to be included
*/
function plugin_getheaderjs_locator()
{
    $scripts = array(
        LOCATOR_PI_PATH . '/js/toggleEnabled.js',
    );
    return $scripts;
}


/**
*   Get plugin-specific CSS into the header
*
*   @return array   Array of filenames to be included
*/
function plugin_getheadercss_locator()
{
    $styles = array(
        LOCATOR_PI_PATH . '/css/style.css',
    );
    return $styles;
}


/**
*   Return the template names that support ad blocks
*/
function plugin_supportadblock_locator()
{
    return array('locator_marker', 'locator_list');
}


/**
*   Use the system message block to display an error message
*   @param  string  $msg    Message to display
*   @param  string  $type   Type or severity of message, default 'info'
*   @param  string  $hdr    Header text, default 'Error'
*   @return string  HTML code to display teh message box
*/
function GEO_errorMsg($msg='', $type='info', $hdr='')
{
    if ($msg == '')
        return '';

    switch ($type) {
        case 'alert':
            $defhdr = 'Alert';
            $class = 'alert';
            break;
        case 'note':
            $defhdr = 'Note';
            $class = 'note';
            break;
        case 'info':
        default:
            $defhdr = 'Information';
            $class = 'info';
            break;
    }

    if ($hdr == '')
        $hdr = $defhdr;

    $display = "<span class=\"$class\">";
    $display .= COM_startBlock($hdr);
    $display .= $msg;
    $display .= COM_endBlock();
    $display .= "</span>";
    return $display;
}


/**
*   Upgrade the plugin
*   Calls locator_do_upgrade() to perform the upgrade if not already
*   at the current revision.
*
*   @return boolean True on success, error code on failure
*/
function plugin_upgrade_locator()
{
    require_once dirname(__FILE__) . '/upgrade.inc.php';
    return locator_do_upgrade();
}


/**
*   Select default group for ad access (viewing and editing)
*
*   @uses   GEO_getGroups()
*   @return array   Array of groups for the selection dropdown
*/
function plugin_configmanager_select_defgrp_locator()
{
    return GEO_getGroups('\'All Users\'', 'NOT');
}


/**
*   Get an array of groups.
*   A comma-separated list of groups can be included or excluded.
*
*   @param string $grplist  Comma-separated list of groups, SQL format
*   @param string $NOT      Set to "NOT" to exclude $grplist, blank to include.
*   @return array           Array of groups.
*/
function GEO_getGroups($grplist='', $NOT='')
{
    global $_TABLES;

    $A = array();

    $query = "SELECT grp_id, grp_name FROM {$_TABLES['groups']}";

    // If a group list is specified, add it as a condition with the
    // specified keyword.
    if ($grplist!= '') {
        $query .= " WHERE grp_name $NOT IN ($grplist)";
    }

    $result = DB_query($query);
    if ($result && DB_numRows($result) > 0) {
        while ($row = DB_fetchArray($result)) {
            $A[$row['grp_name']] = $row['grp_id'];
        }
    }
    return $A;
}


/**
*   Determines if the current user can submit a location
*
*   @return boolean True if submission allowed, False otherwise
*/
function GEO_canSubmit()
{
    global $_CONF, $_CONF_GEO;
    static $cansubmit = NULL;

    if ($cansubmit === NULL) {
        if (plugin_ismoderator_locator()) {
            $cansubmit = true;
        } elseif (
            (COM_isAnonUser() && $_CONF_GEO['submit'] == 2) ||
            (!COM_isAnonUser() && $_CONF_GEO['submit'] > 0) ) {
            $cansubmit = true;
        } else {
            $cansubmit = false;
        }
    }
    return $cansubmit;
}


/**
*   Implements the [locator:] autotag.
*
*   @param  string  $op         Operation to perform
*   @param  string  $content    Page Content
*   @param  string  $autotag    Autotag parameters
*   @return string              HTML generated by autotag
*/
function plugin_autotags_locator($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $_CONF_GEO;

    $lat = '';
    $lng = '';
    $title = '';
    switch ($op) {
    case 'tagname':
        $retval = 'locator';
        break;
    case 'parse':
        $HTML = '';
        $option = trim(COM_applyFilter($autotag['parm1']));
        switch ($option) {
        case 'map':
            // Insert a google map, if configured correctly.  We check this
            // here just to save time looking up markers if we're not.
            if ($_CONF_GEO['show_map'] == 0) {
                break;
            }

            // Don't insert a map into an e-mailed article.  Doesn't work.
            if (isset($_POST['what']) && $_POST['what'] == 'sendstory') {
                break;
            }

            $parm2 = $autotag['parm2'];
            $A = explode(':', $parm2);
            $opt = $A[0];
            $parm3 = isset($A[1]) ? $A[1] : '';
            if (empty($parm3)) {
                $opt = 'id';
                $parm3 = $parm2;
            }
            switch($opt) {
            case 'id':
                $M = new Locator\Marker($parm3);
                if (!$M->isNew) {
                    $lat = $M->lat;
                    $lng = $M->lng;
                    $title = $M->lng;
                }
                break;

            case 'addr':
            case 'address':
                if (!empty($parm3)) {
                    $U = new Locator\UserLoc($parm3, 1);
                    $lat = $U->lat;
                    $lng = $U->lng;
                    $title = $parm3;
                }
                break;
            }
            if (!empty($lat) && !empty($lng)) {
                $HTML = GEO_showMap($lat, $lng, $title);
            }
            break;

        } // switch($option)

        $retval = str_replace($autotag['tagstr'], $HTML, $content);
        break;

    default:
        $retval = $content;
        break;

    }   // switch ($op)

    return $retval;
}


/**
*   Perform scheduled functions, such as deleting user-entered locations
*/
function plugin_runScheduledTask_locator()
{
    global $_TABLES, $_CONF_GEO;

    // Purge user-entered locations if enabled.
    // Only user-entered origin strings (type 1) are deleted.  Origins
    // associated with the location in the user's account settings are
    // left alone.
    $purge_days = 90;
    if ($purge_days > 0) {
        $purge_exp_date = time() - ($purge_days * 86400);
        DB_query("DELETE FROM {$_TABLES['locator_userloc']}
                WHERE type = 1
                AND add_date < DATE_SUB(NOW(), INTERVAL $purge_days DAY)
        ");
    }
}


/**
*   Create a basic map for display.
*
*   @param  float   $lat    Latitude
*   @param  float   $lng    Longitude
*   @param  string  $text   Optional text for marker popup
*   @return string          HTML code for map display
*/
function GEO_showMap($lat, $lng, $text='')
{
    global $_CONF_GEO;

    // Insert a map, if configured correctly
    if ($_CONF_GEO['show_map'] == 0) {
        return '';
    }

    return \Locator\Mapper::getMapper()->showMap($lat, $lng, $text);
}


/**
*   Displays a map in the user's profile.
*
*   @param  integer $uid    User ID whose profile is being displayed
*   @param  object  &$T     Pointer to template object
*   @return string          HTML to include in profile page
*/
function plugin_profilevariablesdisplay_locator($uid, &$T)
{
    global $_CONF_GEO, $_TABLES;

    $retval = '';

    // Make sure that a valid UID was passed in and that we are supposed
    // to show a map in the profile.
    if (!isset($_CONF_GEO['profile_showmap']) ||
        $_CONF_GEO['profile_showmap'] == 0 ||
        $uid < 2) {
        return;
    }

    // Get the user's location if possible.  If none available, return
    $userloc = DB_getItem($_TABLES['userinfo'], 'location', "uid=$uid");
    if ($userloc == '') {
        return;
    }

    $U = new Locator\UserLoc($userloc);

    $LT = new Template(LOCATOR_PI_PATH . '/templates');
    $LT->set_file('page', 'profile_map.thtml');
    $LT->set_var(array(
        'is_uikit'      => $_CONF_GEO['_is_uikit'] ? 'true' : '',
        'profile_map'   => \Locator\Mapper::getMapper()->showMap($U->lat, $U->lng, $userloc),
    ) );
    $LT->parse('output','page');
    $existingFields = $T->get_var('plugin_fields');
    $T->set_var('plugin_fields',
        $existingFields . $LT->finish($LT->get_var('output'))
    );
}


/**
*   Get the link to a documentation file.
*   Returns the URL to the file, or to the directory if no file requested.
*
*   @param  string  $file       HTML file
*   @param  string  $doclang    Documentation language
*   @return string              URL to document directory or file
*/
function LOCATOR_getDocURL($file='', $doclang='english')
{
    global $_CONF, $_CONF_GEO;

    $doc_path = $_CONF['path_html'] . $_CONF_GEO['pi_name'] .
                "/docs/$doclang/";
    $doc_url = '';

    if ($file != '') {
        if (!file_exists($doc_path . $file)) {
            $doclang = 'english';
        }
        $doc_url = LOCATOR_URL . "/docs/$doclang/$file";
    } else {
        if (!is_dir($doc_path)) {
            $doclang = 'english';
        }
        $doc_url = LOCATOR_URL . "/docs/$doclang/";
    }

    return $doc_url;
}


/**
*   Get the URL to the localized configuration help file.
*
*   @uses   LOCATOR_getDocURL()
*   @param  string  $option     Configuration option to display
*   @param  string  $doclang    Documentation language requested
*   @return array   Array of (url, window_type)
*/
function plugin_getconfigelementhelp_locator($option, $doclang='english')
{
    if (version_compare(GVERSION, '1.6.0', '<')) {
        $file = 'config.legacy.html';
    } else {
        $file = 'config.html';
    }
    $doc_url = LOCATOR_getDocURL($file, $doclang);
    if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $doc_url .= '#desc_' . $option;
    }

    return array($doc_url, 0);
}


/**
*   Looks up coordinates from Google for the given address.
*
*   @param  string  $address    Address to look up
*   @param  float   &$lat       Reference to latitude value
*   @param  float   &$lng       Reference to longitude value
*   @return int                 Zero for success, or -1 for failure
*/
function GEO_getCoords($address, &$lat, &$lng)
{
    return \Locator\Mapper::getGeocoder()->geoCode($address, $lat, $lng);
}


/**
*   Returns an array containing the data for a single location.
*   Used as an API function to allow other plugins to look up a location.
*   The returned array will have all items, plus address components.
*
*   @since  1.0.3
*   @param  string  $id         Location ID to retrieve
*   @param  boolean $singleAddress  True to return a single address string,
*                                   False to return array of all info
*   @return mixed   Array of location information, address string, or false on failure
*/
function GEO_getInfo($id, $singleAddress = false)
{
    global $_TABLES;

    // A "select *" is done since we don't know what the caller might want
    $res = DB_query("SELECT *
                FROM {$_TABLES['locator_markers']}
                WHERE id='" . COM_sanitizeId($id) . "'
                AND enabled = 1 " .
                COM_getPermSQL('AND'));
    $A = DB_fetchArray($res, false);
    if (empty($A)) {
        return false;   // Empty result, DB error, whatever.
    }

    if ($singleAddress) {
        $addr = GEO_AddressToString($A);
    } else {
        $addr = $A;
    }
    return $addr;
}


/**
*   Get the URL to Google Maps for inclusion in a template
*   This makes sure the javascript is included only once even if there
*   are multiple maps on the page.
*   Returns the URL, and a random number to be used for the canvas ID.
*
*   @deprecated 1.2.0
*   @return array   $url=>URL to Google Maps javascript, $canvas_id=> random ID
*/
function XGEO_MapJS()
{
    global $_CONF_GEO;

    $canvas_id = rand(1,999);   // Create a random id for the canvas
    if (!defined('GEO_HAVE_MAP_JS')) {
        define('GEO_HAVE_MAP_JS', 1);
        $url = '<script src="' .
            sprintf(GEO_MAP_URL, $_CONF_GEO['google_api_key']) .
            '"></script>';
    } else {
        $url = '';
    }
    return array($url, $canvas_id);
}


/**
*   Return true since this plugin supports webservices
*
*   @return boolean     False, web services are not supported
*/
function plugin_wsEnabled_locator()
{
    return false;
}


/**
*   Support merging local accounts into remote accounts
*
*   @param  integer $origUID    Original (remote) user ID
*   @param  integer $destUID    Merged (local) user ID
*/
function plugin_user_move_locator($origUID, $destUID)
{
    global $_TABLES;

    $origUID = (int)$origUID;
    $destUID = (int)$destUID;

    DB_query("UPDATE {$_TABLES['locator_markers']}
            SET owner_id = $destUID WHERE owner_id = $origUID", 1);
    DB_query("UPDATE {$_TABLES['locator_submission']}
            SET owner_id = $destUID WHERE owner_id = $origUID", 1);
    DB_query("UPDATE {$_TABLES['locator_userXorigin']}
            SET uid = $destUID WHERE uid = $origUID", 1);
}

/**
*   Save the profile information when edited via Account Settings
*/
function plugin_profilesave_locator($uid=0)
{
    global $_TABLES, $_USER;

    if ($uid == 0) {
        $uid = (int)$_USER['uid'];
    }

    // Sanitize data
    $location = isset($_POST['location']) ? DB_escapeString($_POST['location']) : '';

    $U = new \Locator\UserLoc($_POST['location'], $_USER['uid']);
    $U->saveToDB();
}


/**
*   Provides the getItemInfo function for locations
*
*   @param  string  $id         Location ID
*   @param  string  $fields     Comma-separated list of fields to return
*   @param  integer $uid        User ID
*   @param  array   $options    Not used
*   @return array           Array of product information
*/
function plugin_getiteminfo_locator($id, $what, $uid=0, $options=array())
{
    global $_TABLES, $_CONF;

    $properties = explode(',', $what);
    $fields = array();
    $retval = array();
    $buildingSearchIndex = false;
    foreach ($properties as $p) {
        switch ($p) {
        case 'search_index':
            $buildingSearchIndex = true;
            break;
        case 'id':
            $fields[] = 'id';
            break;
        case 'title':
            $fields[] = 'title';
            break;
        case 'content':
        case 'description':
            $fields[] = 'description';
            break;
        case 'searchidx':
            $fields[] = 'title';
            $fields[] = 'address';
            $fields[] = 'city';
            $fields[] = 'state';
            $fields[] = 'postal';
            $fields[] = 'description';
            break;
        case 'author':
            $fields[] = 'owner_id';
            break;
        case 'date':
            $fields[] = 'add_date';
            break;
        }
    }
    if (count($fields) == 0) {
        return $retval;
    }
    $fields = array_unique($fields);
    $fields = implode(',', $fields);
    $retval = array();
    $sql = "SELECT $fields
            FROM {$_TABLES['locator_markers']} ";
    if ($id == '*') {
        $where = ' ';
    } else {
        $where = " WHERE (id = '" . DB_escapeString($id) . "')";
    }
    //echo $sql;die;
    $res = DB_query($sql . $where);
    while ($A = DB_fetchArray($res, false)) {
        foreach ($properties as $p) {
            switch ($p) {
            case 'id':
                $props[$p] = $A['id'];
                break;
            case 'excerpt':
            case 'description':
            case 'content':
                $props[$p] = $A['description'];
                break;
            case 'title':
                $props[$p] = $A['title'];
                break;
            case 'author':
                $props[$p] = $A['owner_id'];
                break;
            case 'date':
                $props[$p] = $A['add_date'];
                break;
            case 'searchidx':
                $props[$p] = $A['title'] . ' ' . $A['address'] . ' ' .
                        $A['city'] . ' ' . $A['state'] . ' ' . $A['postal'] . ' ' .
                        $A['description'];
                break;
            case 'url':
                $props[$p] = $_CONF['site_url'] .
                    '/locator/index.php?detail=x&id=' . urlencode($A['id']);
                break;
            default:
                $props[$p] = isset($A[$p]) ? $A[$p] : NULL;
                break;
            }
        }
        $mapped = array();
        foreach ($props as $key => $value) {
            if ($id == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }
    if ($retval === '' || (is_array($retval) && count($retval) == 0)) {
        return NULL;
    } else {
        return $retval;
    }
}


/**
*   Get a map given a pair of coordinates
*
*   @param  array   $args       Array of item information
*   @param  array   &$output    Pointer to output array
*   @param  array   &$svc_msg   Unused
*   @return integer             Status code
*/
function service_getMap_locator($args, &$output, &$svc_msg)
{
    if (!is_array($args)) return PLG_RET_ERROR;
    $lat = isset($args['lat']) ? (float)$args['lat'] : 0;
    $lng = isset($args['lng']) ? (float)$args['lng'] : 0;
    $text = isset($args['text']) ? $args['text'] : '';
    $output = \Locator\Mapper::getMapper()->showMap($lat, $lng, $text);
    return PLG_RET_OK;
}


/**
*   Get the coordinates for a given address
*
*   @param  array   $args       Array of item information
*   @param  array   &$output    Pointer to output array
*   @param  array   &$svc_msg   Unused
*   @return integer             Status code
*/
function service_getCoords_locator($args, &$output, &$svc_msg)
{
    //if (!is_array($args) || empty($args['address'])) return PLG_RET_ERROR;
    $output = array(
        'lat' => 0,
        'lng' => 0,
    );
    $addr = GEO_AddressToString($args);
    if (GEO_getCoords($addr, $output['lat'], $output['lng']) == 0) {
        return PLG_RET_OK;
    } else {
        return PLG_RET_ERROR;
    }
}


/**
*   Returns a selection list of locations.
*   Intended for other plugins to create a dropdown list.
*
*   @uses   COM_optionList()
*   @param  string  $sel        Currently selected item ID
*   @return string          Options to be bracketd by Select tags
*/
function GEO_optionList($sel='')
{
    global $_TABLES;

    return COM_optionList($_TABLES['locator_markers'], 'id,title', $sel, 1,
        'enabled = 1');
}


/**
*   Returns a selection list of locations.
*   Intended for other plugins to create a dropdown list.
*
*   @uses   GEO_optionList()
*   @param  array   $args       Unused
*   @param  string  &$output    String to hold resulting option list
*   @param  string  &$svc_msg   String to hold any service message
*   @return integer             Just returns OK, $output may be empty at worst
*/
function service_optionList_locator($args, &$output, &$svc_msg)
{
    global $_TABLES;

    $sel = isset($args['selected']) ? $args['selected'] : '';
    $output = GEO_optionList($sel);
    return PLG_RET_OK;
}


/**
*   Return general information about a location
*
*   @uses   GEO_getInfo()
*   @param  array   $args       Array of parameters
*   @param  array   &$output    Array to receive information items
*   @param  string  &$svc_msg   String to hold any service messages
*   @return integer             Status of function call
*/
function service_getInfo_locator($args, &$output, &$svc_msg)
{
    $id = $args['id'];
    $single = isset($args['single']) && $args['single'] ? true : false;
    $output = GEO_getInfo($id, $single);
    if ($output !== false)
        return PLG_RET_OK;
    else
        return PLG_RET_ERROR;
}


/**
*   Convert an array of address elements into a string.
*
*   @param  array   $args   Array of address elements
*   @return string      String form of address
*/
function GEO_AddressToString($args)
{
    if (is_array($args)) {
        // Combine address elements into a comma-delimited string
        $parts = array();
        foreach ($args as $fld) {
            if ($fld != '') $parts[] = $fld;
        }
        $addr = implode(', ', $parts);
    } else {
        // Already a string, just return it
        $addr = $args;
    }
    return $addr;
}


/**
*   Convert a latitude or longitude to a string based on the configured separators.
*
*   @param  float   $val    Value to convert
*   @param  boolean $us     True to force US formatting, False for locale-based
*   @return string      Formatted numeric string
*/
function GEO_coord2str($val, $us = false)
{
    if (!is_numeric($val)) return '';
    if ($us) {
        return number_format($val, 6, '.', ',');
    } else {
        return COM_numberFormat($val, 6);
    }
}


/**
 * Select the mapper to use for rendering maps.
 *
 * @return  array   option => name
 */
function plugin_configmanager_select_mapper_locator()
{
    $mappers = \Locator\Mapper::getMappers();
    foreach ($mappers as $mapper) {
        $A[$mapper->getDisplayName()] = $mapper->getName();
    }
    return $A;
}


/**
 * Select the geocoder to use.
 *
 * @return  array   option => name
 */
function plugin_configmanager_select_geocoder_locator()
{
    $mappers = \Locator\Mapper::getGeocoders();
    foreach ($mappers as $mapper) {
        $A[$mapper->getDisplayName()] = $mapper->getName();
    }
    return $A;
}

?>
