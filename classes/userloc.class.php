<?php
/**
*   User location class for the Locator plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Class to handle the user's location from the glFusion profile
*   @package locator
*/
class UserLoc
{
    /** Properties
    *   @var array */
    var $properties = array();


    /**
    *   Constructor.
    *   Set variables, and read from the database if a location id
    *   is passed in.
    *
    *   @uses   readFromDB()
    *   @uses   getCoords()
    *   @uses   saveToDB()
    *   @param  integer $location   Location ID to read from DB (optional)
    *   @param  integer $type       Type of record. 0=user profile, 1=ad-hoc
    */
    public function __construct($location='', $type=0)
    {
        $this->location = $location;
        $this->id = 0;
        $this->lat = 0;
        $this->lng = 0;
        $this->type = (int)$type;

        // If a location is supplied (it should be), try to read it
        // from the DB.  If it doesn't exist, get its coordinates and
        // save it for future use
        if ($this->location != '') {
            if (!$this->readFromDB()) {
                $this->getCoords();
                $this->saveToDB();
            }
        }
    }


    /**
    *   Magic setter function.
    *   Sets the property value for the named key.
    *
    *   @param  string  $key    Name of property to store
    *   @param  mixed   $value  Value to save for property
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'id':
        case 'type':    // only 0 or 1 now, but may expand types later
            $this->properties[$key] = (int)$value;
            break;

        case 'lat':
        case 'lng':
            $this->properties[$key] = (float)$value;
            break;

        case 'location':
            $value = trim(COM_checkWords(COM_checkHTML($value)));
            $this->properties[$key] = $value;
            break;
        }
    }


    /**
    *   Magic getter function.
    *   Returns the property associated with the provided key.
    *
    *   @param  string  $key    Name of property to return
    *   @return mixed           Value of named property, or NULL if undefined
    */
    public function __get($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        } else {
            return NULL;
        }
    }


    /**
    *   Read the current location from the database
    *
    *   @return boolean     True on success, False on failure or not found
    */
    public function readFromDB()
    {
        global $_TABLES;

        $sql = "SELECT * from {$_TABLES['locator_userloc']}
                WHERE location='" . DB_escapeString($this->location). "'";
                //AND type={$this->type}";
        //echo $sql;die;
        $result = DB_query($sql);
        if ($result && DB_numRows($result) > 0) {
            $row = DB_fetchArray($result, false);
            $this->id = $row['id'];
            $this->lat = $row['lat'];
            $this->lng = $row['lng'];
            return true;
        } else {
            return false;
        }
    }


    /**
    *   Save the current variables to the database.
    *   The update portion is here for completeness and future use,
    *   but should not currently be needed as there is no editing function.
    */
    public function saveToDB()
    {
        global $_TABLES;

        $lat = number_format($this->lat, 6, '.', '');
        $lng = number_format($this->lng, 6, '.', '');

        if ($this->id == 0) {
            $sql = "INSERT INTO {$_TABLES['locator_userloc']}
                        (type, location, lat, lng)
                    VALUES (" .
                        $this->type . ", 
                        '" . DB_escapeString($this->location) . "', 
                        '{$lat}',
                        '{$lng}'
                    )";
        } else {    // For completeness, shouldn't be called.
            $sql = "UPDATE {$_TABLES['locator_userloc']} SET
                    location = '" . DB_escapeString($this->location) . "',
                    lat = '{$lat}',
                    lng = '{$lng}',
                    WHERE id={$this->id}";
        }
        //echo $sql;die;
        DB_query($sql);
    }


    /**
    *   Retrieve the coordinates for the current location.
    *   Sets the local $lat and $lng variables
    *
    *   @uses   GEO_getCoords()
    *   @return integer Google return code, or false for failure.
    */
    public function getCoords()
    {
        global $_CONF_GEO;

        // Check for valid Google config items, and if we're configured
        // to automatically geocode addresses
        if ($_CONF_GEO['autofill_coord'] == 0) {
            return 0;
        }

        // Insert the location and Google API key into the url.
        $address = urlencode($this->location);
        $lat = 0;
        $lng = 0;
        GEO_getCoords($address, $lat, $lng);
        $this->lat = $lat;
        $this->lng = $lng;
    }

}   // class UserLoc


/**
*   Class to handle the user-entered strings used as search origins.
*   @package locator
*/
class UserOrigin extends UserLoc
{
    /**
     *  Constructor
     *  Calls the parent constructor and sets the record type to '1'
     */
    public function __construct($location='')
    {
        parent::__construct($location, 1);
    }

}


?>
