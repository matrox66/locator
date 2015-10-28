<?php
/**
*   Public entry point for the Locator plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    0.1.2
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/** Include required common glFusion functions */
require_once '../lib-common.php';

// If plugin is installed but not enabled, display an error and exit
// Also exit if the plugin is enabled for API use only and not guest-facing
if (!in_array('locator', $_PLUGINS) ||
    (isset($_GEO_CONF['api_only']) && $_GEO_CONF['api_only'] == 1)
) {
    COM_404();
    exit;
}

// If login is required, but user is anonymous, show the login form
if ($_CONF['loginrequired'] == 1 && COM_isAnonUser()) {
    SEC_loginRequiredForm();
    exit;
}

/** Include plugin-specific functions */
USES_locator_functions();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$display = '';

// Retrieve and sanitize arguments and form vars
$action = '';
$actionval = '';
$expected = array(
    'savemarker', 'detail', 'myorigins', 
    'mode', 
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
    	$action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}
if ($action == 'mode') {
    $action = $actionval;
}
$origin = isset($_REQUEST['origin']) ? COM_sanitizeID($_REQUEST['origin']) : '';
$id = isset($_REQUEST['id']) ? COM_sanitizeID($_REQUEST['id']) : '';
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : $action;
$content = '';

switch($action) {
case 'savemarker':
    USES_locator_class_marker();
    if (isset($_POST['oldid']) && !empty($_POST['oldid'])) {
        $M = new Marker($_POST['oldid']);
    } else {
        $M = new Marker();
    }
    if (SEC_hasRights($_CONF_GEO['pi_name'].'.admin')) {
        $table = 'locator_markers';
        $success_msg = 2;
    } else {
        $table = 'locator_submission';
        $success_msg = 1;
    }
    $msg = $M->Save($_POST, $table);
    if (empty($msg)) {
        $msg = $success_msg;
    } else {
        $msg = 99;
    }

    // Unset variables that were used in the form, otherwise we'll immediately
    // try to search for this record, which fails if we saved to the
    // submission table.
    unset($_REQUEST);
    unset($_POST);
    unset($_GET);
    $view = '';
    break;

case 'toggleorigin':
    $newval = (int)$_REQUEST['is_origin'];
    if ($newval == 0) {
        GEO_delUserOrigin($id);
    } else {
        GEO_addUserOrigin($id);
    }
    $view = 'myorigins';
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'myorigins':
    $content .= GEO_showOrigins();
    break;

case 'detail':
    USES_locator_class_marker();
    $M = new Marker($id);
    $content .= $M->Detail($origin);
    break;

case 'loclist':
default:
    $radius = isset($_REQUEST['radius']) ? (int)$_REQUEST['radius'] : 0;
    $keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
    if (isset($_REQUEST['units']) && 
        in_array($_REQUEST['units'], array('km', 'miles'))
    ) {
        $units = $_REQUEST['units'];
    } else {
        $units = $_CONF_GEO['distance_unit'];
    }
    $address = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';
    $content .= GEO_showLocations($origin, $radius, $units, $keywords, $address);
    break;

}

$display .= GEO_siteHeader();

if (!empty($msg)) {
    $display .= COM_showMessage((int)$msg, 'locator');
}

$T = new Template($_CONF['path'] . 'plugins/locator/templates');
$T->set_file('page', 'locator_header.thtml');
if (!COM_isAnonUser()) {
    $T->set_var('url_myorigins', 
            "<a href=\"{$_SERVER['PHP_SELF']}?myorigins=x\">" .
                $LANG_GEO['my_origins']. '</a>');
}
if (!COM_isAnonUser() || $_CONF_GEO['anon_submit'] == 1) {
    $T->set_var('url_contrib', 
            '<a href="' . $_CONF['site_url'] . '/submit.php?type=' .
            $_CONF_GEO['pi_name'] . '">' .$LANG_GEO['contrib_origin'] . 
            '</a>' . LB);
}
$T->set_var('url_home', LOCATOR_URL . '/index.php');
$T->parse('output', 'page');
$display .= $T->finish($T->get_var('output'));
$display .= $content;

$display .= GEO_siteFooter();
echo $display;

?>
