<?php
/**
 * Class for Google Maps
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
class google extends \Locator\Mapper
{
    private $_lang;
    // URL to maps javascript
    const GEO_MAP_URL = 'https://maps.google.com/maps/api/js?key=%s';
    // Geocoding url, address will be appended to this
    const GEO_GOOG_URL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    /**
    *   Constructor
    *
    *   @param  string  $id     Optional ID of a location to load
    */
    public function __construct($id = '')
    {
        // Load supported languages
        $this->_lang = array(
            'ar',
            'bg', 'bn',
            'ca', 'cs',
            'da', 'de',
            'el', 'en', 'en-AU', 'en-GB', 'es', 'eu',
            'fa', 'fi', 'fil', 'fr',
            'gl', 'gu', 'hi', 'hr', 'hu',
            'id', 'it', 'iw',
            'ja',
            'kn', 'ko',
            'lt', 'lv',
            'ml', 'mr',
            'nl', 'no',
            'pl', 'pt', 'pt-BR', 'pt-PT',
            'ro', 'ru',
            'sk', 'sl', 'sr', 'sv',
            'tl', 'ta', 'te', 'th', 'tr',
            'uk',
            'vi',
            'zh-CN', 'zh-TW',
        );
    }


    /**
     * Display a map.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @param   string  $tpl    Template base name (large or small)
     * @return  string          HTML to generate the map
     */
    public function showMap($lat, $lng, $text = '', $tpl = 'small')
    {
        global $_CONF_GEO, $_CONF;

        // Insert a google map, if configured correctly
        if ($_CONF_GEO['show_map'] == 0) {
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0)
        return '';

        // Check that the site language is supported by Google,
        // default to English if not.
        $iso_lang = in_array($_CONF['iso_lang'], $this->_lang) ?
                $_CONF['iso_lang'] : 'en';

        list($js_url, $canvas_id) = $this->getMapJS();
        $T = new \Template(LOCATOR_PI_PATH . '/templates/google');
        $T->set_file('page', $tpl . '_map.thtml');
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'infowindow_text' => COM_checkHTML($text),
            'iso_lang'      => $iso_lang,
            'geo_map_js_url' => $js_url,
            'canvas_id'     => $canvas_id,
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
    }

    
    public function geoCode($address, &$lat, &$lng)
    {
        global $_CONF_GEO;

        if ($_CONF_GEO['autofill_coord'] != 1 || empty($address))
            return 0;

        $address = urlencode(GEO_AddressToString($address));
        if (version_compare(GVERSION, '1.8.0', '>=')) {
            $c = \glFusion\Cache::getInstance();
            $key = md5($address);
            if ($c->has($key)) {
                $coords = $c->get($key);
                $lat = $coords['lat'];
                $lng = $coords['lng'];
                return 0;
            }
        }
        $url = self::GEO_GOOG_URL . $address . '&key=' . $_CONF_GEO['google_api_key'];
        $json = GEO_file_get_contents($url);
        if ($json == '') {
            return 0;
        }
        $data = json_decode($json, true);
        if (!is_array($data) || $data['status'] != 'OK') {
            return -1;
        }
        $lat = $data['results'][0]['geometry']['location']['lat'];
        $lng = $data['results'][0]['geometry']['location']['lng'];
        if (version_compare(GVERSION, '1.8.0', '>=')) {
            $c = \glFusion\Cache::getInstance();
            $c->set($key, array('lat'=>$lat, 'lng'=>$lng), 'locator');
        }
        return 0;
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
            $url = '<script src="' .
                sprintf(self::GEO_MAP_URL, $_CONF_GEO['google_api_key']) .
                '"></script>';
        } else {
            $url = '';
        }
        return array($url, $canvas_id);
    }

}

?>
