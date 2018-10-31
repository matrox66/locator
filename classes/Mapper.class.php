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


    /**
    *   Retrieve the contents of a remote URL.
    *
    *   @param  string  $url    URL to retrieve
    *   @return string          Raw contents from URL
    */
    public static function getUrl($url)
    {
        if (in_array('curl', get_loaded_extensions())) {
            $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; ' .
                    'rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR ' .
                    '3.5.30729)';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $url);
            curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
            curl_setopt($ch, CURLOPT_HEADER,         0);
            curl_setopt($ch, CURLOPT_ENCODING,       "gzip");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT,        8);

            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result = '';
            COM_errorLog('LOCATOR: Missing url_fopen and curl support');
        }
        return $result;
    }

}   // class Mapper

?>
