<?php
/**
*   SQL Commands for the GeoLoc Plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2010 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Define tables used by the Locator plugin
*   @global array $_SQL
*/
$_SQL['locator_markers'] = 
"CREATE TABLE {$_TABLES['locator_markers']} (
  `id` varchar(20) NOT NULL default '',
  `owner_id` mediumint(8) unsigned default NULL,
  `title` varchar(60) NOT NULL default '',
  `address` varchar(80) NOT NULL,
  `description` text,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `is_origin` tinyint(1) default '0',
  `keywords` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `views` mediumint(8) NOT NULL default '0',
  `add_date` int(11) NOT NULL default '0',
  `group_id` mediumint(8) unsigned default NULL,
  `perm_owner` tinyint(1) unsigned NOT NULL default '3',
  `perm_group` tinyint(1) unsigned NOT NULL default '3',
  `perm_members` tinyint(1) unsigned NOT NULL default '2',
  `perm_anon` tinyint(1) unsigned NOT NULL default '2',
  `enabled` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
)";

/** Marker submission table */
$_SQL['locator_submission'] = 
"CREATE TABLE `{$_TABLES['locator_submission']}` (
  `id` varchar(20) NOT NULL default '',
  `owner_id` mediumint(8) unsigned default NULL,
  `title` varchar(60) NOT NULL default '',
  `address` varchar(80) NOT NULL,
  `description` text,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `is_origin` tinyint(1) default '0',
  `keywords` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `add_date` int(11) NOT NULL default '0',
  `group_id` mediumint(8) unsigned default NULL,
  `perm_owner` tinyint(1) unsigned NOT NULL default '3',
  `perm_group` tinyint(1) unsigned NOT NULL default '3',
  `perm_members` tinyint(1) unsigned NOT NULL default '2',
  `perm_anon` tinyint(1) unsigned NOT NULL default '2',
  `enabled` tinyint(1) unsigned NOT NULL default '1'
)";

/** Table to hold user's selected origins. */
$_SQL['locator_userXorigin'] = 
"CREATE TABLE `{$_TABLES['locator_userXorigin']}` (
  `id` mediumint(8) NOT NULL auto_increment,
  `uid` mediumint(8) default NULL,
  `mid` varchar(20) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idxUID` (`uid`)
)";

/** Cache table to hold coordinates of user locations */
$_SQL['locator_userloc'] = 
"CREATE TABLE `{$_TABLES['locator_userloc']}` (
  `id` int(11) NOT NULL auto_increment,
  `type` tinyint(1) default '0',
  `location` varchar(80) default NULL,
  `add_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `lat` float(10,6) default '0.000000',
  `lng` float(10,6) default '0.000000',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `location` (`location`)
)";

?>
