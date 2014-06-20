<?php
/**
*   Edit a location marker
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/

/**
*   Display the marker editing form
*   @param  string  $mode   Mode of submission
*   @param  array   $A      Array of existing values
*   @param  boolean @admin  True if this is an administrator edit
*   @return string  HTML code for ediing form
*/
function GEO_markerForm($mode='submit', $A='', $admin=false)
{
    global $_TABLES, $_CONF, $_USER, $LANG_GEO, $_CONF_GEO,
            $LANG24, $LANG_postmodes, $LANG_ADMIN, $LANG12;

    $retval = '';
    if ($A == '') $A = array();

    switch ($mode) {
    case 'edit':
        $saveoption = $LANG_ADMIN['save'];      // Save
        $sub_type = '<input type="hidden" name="item" value="locator" />';
        $cancel_url = $admin ? LOCATOR_ADMIN_URL . '/index.php' :
                $_CONF['site_url'];
    case 'submit':
        $saveoption = $LANG_ADMIN['save'];      // Save
        // override sub_type for submit.php
        $sub_type = 
            '<input type="hidden" name="type" value="locator" />' .
            '<input type="hidden" name="mode" value="' .
            $LANG12[8].'" />';
        $cancel_url = $admin ? LOCATOR_ADMIN_URL . '/index.php' :
                $_CONF['site_url'];
        break;

    case 'moderate':
        $saveoption = $LANG_ADMIN['moderate'];  // Save & Approve
        $sub_type = '<input type="hidden" name="type" value="submission" />';
        $cancel_url = $_CONF['site_admin_url'] . '/moderation.php';
        break;
    }


    if (isset($_CONF['advanced_editor']) && $_CONF['advanced_editor'] == 1) {
        $editor_type = '_advanced';
        $postmode_adv = 'selected="selected"';
        $postmode_html = '';
    } else {
        $editor_type = '';
        $postmode_adv = '';
        $postmode_html = 'selected="selected"';
    }

    //displays the add quote form for single quotations
    $T = new Template($_CONF['path'] . 'plugins/' . 
            $_CONF_GEO['pi_name'] . '/templates');
    //$T->set_file('page', 'markerform.thtml');
    $T->set_file('page', "markerform{$editor_type}.thtml");

    // Load existing values, if any.  Cleanse string form values
    if (is_array($A) && !empty($A)) {
        $A['title'] = htmlspecialchars($A['title']);
        $A['description'] = htmlspecialchars($A['description']);
        $A['address'] = htmlspecialchars($A['address']);
        $A['lat'] = htmlspecialchars($A['lat']);
        $A['lng'] = htmlspecialchars($A['lng']);
        $A['keywords'] = htmlspecialchars($A['keywords']);
        $A['url'] = htmlspecialchars($A['url']);
        $latlng = join(',', array($A['lat'], $A['lng']));
    } else {
        // Initialize blank values and defaults
        $A['title'] = '';
        $A['description'] = '';
        $A['address'] = '';
        $A['lat'] = '';
        $A['lng'] = '';
        $A['keywords'] = '';
        $A['url'] = '';
        $A['id'] = '';
        $A['is_origin'] = 0;
        $A['enabled'] = 1;
        $A['perm_owner'] = 3;
        $A['perm_group'] = 3;
        $A['perm_members'] = 2;
        $A['perm_anon'] = 2;
        $A['owner_id'] = $_USER['uid'];
        $A['group_id'] = $_CONF_GEO['defgrp'];
        $latlng = '';
    }

    // Set up the advanced editor stuff
    $post_options = "<option value=\"html\" $postmode_html>{$LANG_postmodes['html']}</option>";
    $post_options .= "<option value=\"adveditor\" $postmode_adv>{$LANG24[86]}</option>";

    $T->set_var(array(
        'title'     => $A['title'],
        'description'   => $A['description'],
        'address'   => $A['address'],
        'lat'       => $A['lat'],
        'lng'       => $A['lng'],
        'latlng'    => $latlng,
        'keywords'  => $A['keywords'],
        'url'       => $A['url'],
        'id'        => $A['id'],
        'hidden_vars' =>
            '<input type="hidden" name="oldid" value="'. $A['id'] . '">',
        'origin_chk'    => $A['is_origin'] == 1 ? ' checked ' : '',
        'enabled_chk'   => $A['enabled'] == 1 ? ' checked ' : '',

        'glfusionStyleBasePath' => $_CONF['site_url']. '/fckeditor',
        'gltoken_name'  => CSRF_TOKEN,
        'gltoken'       => SEC_createToken(),
        'change_editormode' => 'onchange="change_editmode(this);"',
        'post_options'  => $post_options,
        'permissions_editor' => SEC_getPermissionsHTML(
                            $A['perm_owner'],$A['perm_group'],
                            $A['perm_members'],$A['perm_anon']),
        'site_url'      => $_CONF['site_url'],
        'pi_name'       => $_CONF_GEO['pi_name'],
        'action'        => $mode,
    ) );

    if ($editor_type == '_advanced') {
        $T->set_var('show_adveditor','');
        $T->set_var('show_htmleditor','none');
    } else {
        $T->set_var('show_adveditor','none');
        $T->set_var('show_htmleditor','');
    }

    if ($_CONF_GEO['autofill_coord'] != '') {
        $T->set_var('goog_map_instr', $LANG_GEO['coord_instr2']);
    }

    if ($admin) {
        $T->set_var('action_url', $_CONF['site_admin_url']. '/plugins/'. 
                $_CONF_GEO['pi_name']. '/index.php');
        // Set up owner selection
        $T->set_var('ownerselect', GEO_userDropdown($A['owner_id']));
        $T->set_var('group_dropdown', SEC_getGroupDropdown($A['group_id'], 3));
    } else {
        $T->set_var('action_url', $_CONF['site_url']. '/submit.php');
        $T->set_var('owner_id', $A['owner_id']);
        $T->set_var('group_id', $A['group_id']);
        $T->set_var('ownername', COM_getDisplayName($A['owner_id']));
        $T->set_var('groupname', DB_getItem($_TABLES['groups'], 'grp_name',
                "grp_id={$A['group_id']}"));
    }

    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));

    $T->set_file('page', 'closemarkerform.thtml');
    $T->set_var(array(
        'submission_option' => $sub_type,
        'lang_save'     => $saveoption,
        'cancel_url'    => $cancel_url,
    ) );

    if ($admin && !empty($A['id']) && $mode != 'submit') {
        $T->set_var('show_del_btn', 'true');
    } else {
        $T->set_var('show_del_btn', '');
    }

    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}


/**
*   Returns the <option></option> portion to be used
*   within a <select></select> block to choose users from a dropdown list
*
*   @param string $selID    ID of selected value
*   @returns string         HTML output containing options
*/
function GEO_userDropdown($selId = '')
{
    global $_TABLES;

    // Find users, excluding anonymous
    $sql = "SELECT uid FROM {$_TABLES['users']}
            WHERE uid > 1";
    $result = DB_query($sql);
    if (!$result)
        return '';

    $retval = '';
    while ($row = DB_fetchArray($result)) {
        $name = COM_getDisplayName($row['uid']);
        $sel = $row['uid'] == $selId ? 'selected' : '';
        $retval .= "<option value=\"{$row['uid']}\" $sel>$name</option>\n";
    }

    return $retval;

}   // function GEO_userDropdown()


?>
