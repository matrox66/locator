# locator
Geo Locator plugin for glFusion

Creates a "store locator"-type function where visitors can find locations
within a distance range from an origin location.

Also provides an autotag to allow inclusion of Google maps in stories and staticpages,
and an API for other plugins such as Evlist.

Requires the "lglib" plugin for some internal functions such as message handling.

## API Key
You will need to create an API key for the map provider of your choice and enter it in the plugin configuration.
  * https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key
    - Enable Billing on your Google Account. There is a charge for using the Google APIs.
    * Enable Maps JavaScript API and Geocoding API
    * Create two API Keys
      * Geocoding API key can be restricted to the public IP of your server
      * Maps JS key should be restricted by HTTP Referer to your domain
  * https://developer.mapquest.com/documentation/
