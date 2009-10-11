=== Weekly Schedule ===
Contributors: jackdewey
Donate link: http://yannickcorner.nayanna.biz/wordpress-plugins/weekly-schedule/
Tags: schedule, grid, weekly, tooltip, jQuery
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: trunk

The purpose of this plugin is to allow users to create a schedule of weekly events and display that schedule on a page in a table form. Users can style events using stylesheets based on their category and can assign information to items that will be displayed in a tooltip.

== Description ==

The purpose of this plugin is to allow users to create a schedule of weekly events and display that schedule on a page in a table form. Users can style events using stylesheets based on their category and can assign information to items that will be displayed in a tooltip.

You can see a demonstration of the output of the plugin [here](http://yannickcorner.nayanna.biz/2009-2010-tv-schedule/).

== Installation ==

1. Download the plugin and unzip it.
1. Upload the tune-library folder to the /wp-content/plugins/ directory of your web site.
1. Activate the plugin in the Wordpress Admin.
1. Using the Configuration Panel for the plugin, create schedule categories and items
1. To see your schedule, in the Wordpress Admin, create a new page containing the following code:<br/>
   [weekly-schedule]

== Changelog ==

= 1.1.1 =
* Corrected bugs with verfication of conflicting items upon addition or deletion of items

= 1.1 =
* Adds new vertical display option
* 24/12 hour display mode is reflected in admin interface
* Various bug fixes

= 1.0.1 =
* Added option to choose between 24 hour and 12 hour time display
* Fixed link to settings panel from Plugins page

= 1.0 = 
* First release

== Frequently Asked Questions ==

= How do I style the items belonging to a category? =

Create an entry in the stylesheet called cat# where # should be replaced with the category ID.

= How do I add images to the tooltip =

Use HTML codes in the item description to load images or do any other type of HTML formatting.

== Screenshots ==

1. A sample schedule created with Weekly Schedule
2. General Plugin Configuration
3. Manage and add items to the schedule
