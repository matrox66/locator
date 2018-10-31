<?php
/**
 * Class for Mapquest Map provider
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.1.4
 * @since       1.1.4
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;

/**
*   Class to handle the general location markers
*   @package    locator
*/
class mapquest extends \Locator\Mapper
{
    private $client_key = NULL;
    const GEOCODE_URL = 'http://www.mapquestapi.com/geocoding/v1/address?inFormat=kvp&outFormat=json&key=%s&location=%s';

    /**
    *   Constructor
    *
    *   @param  string  $id     Optional ID of a location to load
    */
    public function __construct($id = '')
    {
        global $_CONF_GEO;
        if (isset($_CONF_GEO['mapquest_key'])) {
            $this->client_key = $_CONF_GEO['mapquest_key'];
        }
    }


    /**
     * Display a map.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @return  string          HTML to generate the map
     */
    public function showMap($lat, $lng, $text = '', $tpl = 'small')
    {
        global $_CONF_GEO, $_CONF;

        // Insert a google map, if configured correctly
        if ($_CONF_GEO['show_map'] == 0) {
            return '';
        }
        if ($this->client_key === NULL) {
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0) {
            return '';
        }

        list($js_url, $canvas_id) = $this->getMapJS();
        COM_errorLog("$lat --- $lng");
        $T = new \Template(LOCATOR_PI_PATH . '/templates/mapquest');
        $T->set_file('page', $tpl . '_map.thtml');
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'geo_map_js_url' => $js_url,
            'canvas_id'     => $canvas_id,
            'client_key'    => $this->client_key,
            'directions'    => $_CONF_GEO['use_directions'] ? true : false,
            'text'          => $text,
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Get the coordinates from an address string.
     *
     * @param   string  $address    Address string
     * @param   float   &$lat       Latitude return var
     * @param   float   &$lng       Longitude return var
     * @return  integer             0 for success, nonzero for failure
     */
    public function geoCode($address, &$lat, &$lng)
    {
        $cache_key = 'mapquest_geocode_' . md5($address);
        $loc = \Locator\Cache::get($cache_key);
        if ($loc === NULL) {
            $url = sprintf(self::GEOCODE_URL, $this->client_key, urlencode($address));
            $json = GEO_file_get_contents($url);
            if ($json == false) {
                return 0;
            }
            $data = json_decode($json, true);
            if (!is_array($data) || !isset($data['info']['statuscode']) || $data['info']['statuscode'] != 0) {
                return -1;
            }
            if (!isset($data['results'][0]['locations']) || !is_array($data['results'][0]['locations'])) {
                return -1;
            }
            $conf_code = 'ZZZ';     // Initilalize
            $loc = NULL;
            foreach ($data['results'][0]['locations'] as $loc_data) {
                $loc_conf_code = substr($loc_data['geocodeQualityCode'], -3);
                if ($loc_conf_code < $conf_code) {
                    $conf_code = $loc_conf_code;
                    $loc = $loc_data;
                }
            }
            \Locator\Cache::set($cache_key, $loc);
        }

        if (!isset($loc['latLng']) || !is_array($loc['latLng'])) {
            $lat = 0;
            $lng = 0;
            return -1;
        } else {
            $lat = $loc['latLng']['lat'];
            $lng = $loc['latLng']['lng'];
            return 0;
        }
    }


    /**
     * Get the URL to Google Maps for inclusion in a template.
     * This makes sure the javascript is included only once even if there
     * are multiple maps on the page.
     * Returns the URL, and a random number to be used for the canvas ID.
     *
     * @return  array   $url=>URL to Google Maps javascript, $canvas_id=> random ID
     */
    private function getMapJS()
    {
        global $_CONF_GEO;
        static $have_map_js = false;    // Flag to avoid duplicate loading

        $canvas_id = rand(1,999);   // Create a random id for the canvas
        if (!$have_map_js) {
            $have_map_js = true;
            $url = '<script src="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.js"></script>' . LB;
            $url .= '<link type="text/css" rel="stylesheet" href="https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.css"/>' . LB;
        } else {
            $url = '';
        }
        return array($url, $canvas_id);
    }

}

?>
