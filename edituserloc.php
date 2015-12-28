<?php
/**
*   Edit a marker
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Displays the edit form for user locations.  
*   Only admins have access to this form.
*
*   @param  array   $A  Array returned from a DB lookup, or empty string.
*   @return string      HTML for the user location editing form.
*/
function GEO_userlocForm($A='')
{
    global $_TABLES, $_CONF, $_USER, $LANG_GEO, $_CONF_GEO, $_SYSTEM;

    $retval = '';
    if ($A == '') $A = array();

    //displays the add quote form for single quotations
    $T = new Template($_CONF['path'] . 'plugins/' . 
            $_CONF_GEO['pi_name'] . '/templates');
    $T->set_file('page', 'userlocform.thtml');

    // Load existing values, if any.  Prepare for use in the form
    if (is_array($A) && !empty($A)) {
        $A['location'] = htmlspecialchars($A['location']);
        $A['lat'] = (double)$A['lat'];
        $A['lng'] = (double)$A['lng'];
        $A['latlng'] = join(',', array($A['lat'], $A['lng']));
    } else {
        // Initialize blank values and defaults
        $A['location'] = '';
        $A['lat'] = '';
        $A['lng'] = '';
        $A['latlng'] = '';
    }

    $T->set_var(array(
        'location'  => $A['location'],
        'lat'       => $A['lat'],
        'lng'       => $A['lng'],
        'latlng'    => $latlng,
        'frm_id'    => $A['id'],
        'goog_map_instr' => $_CONF_GEO['autofill_coord'] != '' ? 
                    $LANG_GEO['coord_instr2'] : '',
        'action_url' => $_CONF['site_admin_url']. '/plugins/'. 
                $_CONF_GEO['pi_name']. '/index.php',
        'pi_name'   => $_CONF_GEO['pi_name'],
        'action'    => 'saveuserloc',
        'show_del_btn' => !empty($A['id']) ? 'true' : '',
        'mootools' => $_SYSTEM['disable_mootools'] ? '' : 'true',
    ) );
    $T->parse('output','page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}


?>
