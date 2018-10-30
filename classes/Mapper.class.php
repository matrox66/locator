<?php
/**
*   Base class for mappers. Mainly used to instantiate the configured mapper.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.4
*   @since      1.1.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/
namespace Locator;

/**
*   Base class to return a Mapper
*   @package locator
*/
class Mapper
{
    public static function getInstance($name='')
    {
        global $_CONF_GEO;
        static $mappers = array();

        if ($name == '') $name = $_CONF_GEO['mapper'];
        if (!isset($mappers[$name])) {
            $clsname = '\\Locator\\Mappers\\' . $name;
            if (class_exists($clsname)) {
                $mappers[$name] = new $clsname();
            } else {
                $mappers[$name] = new self;
            }
        }
        return $mappers[$name];
    }


    /**
     * Default function to show a map, in case an invalid class
     * was instantiated.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @return  string          Empty string
     */
    public function showMap($lat, $lng, $text = '', $tpl = '')
    {
        return '';
    }


    public function showLargeMap($lat, $lng, $text = '')
    {
        return '';
    }

}   // class Mapper 

?>
