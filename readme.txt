=== Weekly Schedule ===
Contributors: jackdewey
Donate link: http://yannickcorner.nayanna.biz/wordpress-plugins/weekly-schedule/
Tags: schedule, events, grid, weekly, multiple, tooltip, jQuery
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk

The purpose of this plugin is to allow users to create a schedule of weekly events and display that schedule on a page in a table form. Users can style events using stylesheets based on their category and can assign information to items that will be displayed in a tooltip.

== Description ==

The purpose of this plugin is to allow users to create one or more schedules of weekly events and display these schedule on one or more pages as tables. Users can style their schedules using stylesheets based on the category of items and can assign information to items that will be displayed in a tooltip.

You can see a demonstration of the output of the plugin using a single schedule [here](http://yannickcorner.nayanna.biz/2009-2010-tv-schedule/).

== Installation ==

1. Download the plugin and unzip it.
1. Upload the tune-library folder to the /wp-content/plugins/ directory of your web site.
1. Activate the plugin in the Wordpress Admin.
1. Using the Configuration Panel for the plugin, create schedule categories and items
1. To see your schedule, in the Wordpress Admin, create a new page containing the following code:<br/>
   [weekly-schedule schedule=1]<br />
   where the schedule number will change based on the number of schedules defined.

== Changelog ==

= 2.2.1 =
* Added support for 2-hour and 3-hour time divisions

= 2.2 =
* Updated qTip plugin for compatibility with Wordpress 3.0

= 2.1.2 =
* Fixed: Could not save general settings for schedules other than #1.

= 2.1.1 =
* Fixed: Times not showing well when listing schedule items

= 2.1 =
* Added: Ability to set time division to 15 minutes (Thanks to Matt Bryers for suggestion and initial ground work)

= 2.0.2 =
* Added reference links at top of admin page

= 2.0.1 =
* Added extra styles to work with times up to 4 hours in vertical mode

= 2.0 =
* New Feature: Added ability to define and display multiple schedules on a Wordpress page

= 1.1.8 =
* Fixed: 12:30pm was showing as 0:30pm.
* Tested with Wordpress 3.0 Beta 1

= 1.1.7 =
* Only load stylesheets and scripts if necessary

= 1.1.6 =
* Corrected problem with creation of tables on installation
* Corrected problem of lost settings on upgrade

= 1.1.5 =
* Restored ability to put HTML codes in item names

= 1.1.4 =
* Now allows descriptions and item names to contain quotes and other special html characters

= 1.1.3 =
* Added option for tooltip position to be automatically adjusted to be in visible area.

= 1.1.2 =
* Removed debugging statements from admin interface and generated output

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
