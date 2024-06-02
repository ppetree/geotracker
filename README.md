# geoTracker Version 1.9.0
**GeoTracker** is a Joomla 4.0+ module that captures the users location and displays it on a google map.

To do this, it captures the visitors IP address and uses either ipapi.com or ip2location.io (preferred) to retrieve visitors approximate latitude and longitude coordinates and then stores those coordinates in a database. A cookie is then saved marking this as 'done' and so we don't duplicate lookups.

To implement this you will need two modules:

**Module One:** In this module 'Hide Map - Capture Data' should be selected and 'Title' should be set to NO and then assigned to any module position on all the pages where you want to capture the visitors location (home page, landing pages etc).

**Module Two:** This second module will have 'Show Maps' enabled and this module will be assigned to the page where you want your map displayed. Something like 'geotracker-map'

You will need a free account at ip2location.io (peferred) or ipapi.com (less secure, fewer lookups) plus you will need your Google Maps key. All of that data is entered on the _Advanced_ tab

Lastly, you will probably want to keep the menu item showing this map off the public lookups and out of search engines so that random visitors don't run up your maps usage. 

**New Features in Version 1.9.0**
In version 1.9.0 I added a counter to the database along with a timestamp. This will now track how many times a visitor has been to your site and when they last visited. Creating a module using this setting will include the most recent visitors (both new and repeat) while disabling this setting will show you the most recent new visitors.

**Road Map**
I implemented a product road map which you can find above. I detailed the plans I have for new functionality.
