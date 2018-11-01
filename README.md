# locator
Geo Locator plugin for glFusion

## Features
* Creates a "store locator"-type function where visitors can find locations
within a distance range from an origin location.
* Provides an autotag to allow inclusion of maps in stories and staticpages,
and an API for other plugins such as Evlist.

## Requirements
* glFusion 1.7.0+
* PHP 7.0+
* LGLib plugin

## Provider Configuration
Several Geocoding and Mapping providers are included with the plugin. You can mix and match
them to meet your needs as some have different requirements or capabilities than others.

Driving directions are always provided by maps.google.com.

* Google (https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key)
  * Requires API keys and Billing enabled on your account.
  * You should use different API keys for Geocoding and Mapping and restrict them appropriately to your server's address and HTTP Referer, respectively, to prevent &quot;quota theft&quot;.
  * Geocoding: Yes
  * Mapping: Yes
* MapQuest (https://developer.mapquest.com/documentation/)
  * Requires an API key.
  * Terms of service do not allow for caching or using coordinates for any purpose other than mapping. Unless you have an Enhanced plan you should not use MapQuest for geocoding.
  * Geocoding: Yes
  * Mapping: Yes
* U.S. Census (https://geocoding.geo.census.gov/)
  * Only supports locations in the United States
  * Geocoding: Yes
  * Mapping: No
* Geocodio (https://www.geocod.io)
  * USA and Canada
  * API key required, free 2500 lookups per day
  * Geocoding: Yes
  * Mapping: No
* OpenStreetMap (https://www.openstreetmap.org)
  * Check the site for terms and conditions, light usage is expected
  * Geocoding: Yes
  * Mapping: Yes
