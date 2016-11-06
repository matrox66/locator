# locator
Geo Locator plugin for glFusion

Creates a "store locator"-type function where visitors can find locations
within a distance range from an origin location.

Also provides an autotag to allow inclusion of Google maps in stories and staticpages,
and an API for other plugins such as Evlist.

Requires the "lglib" plugin for some internal functions such as message handling.

## API Key
There is a plugin configuration item where you can enter a Google Maps API key.
At this time Google does not appear to require a key for the Maps API, but if this
changes in the future you can enter your key in the space proviced.
