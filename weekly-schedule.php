<?php
/*Plugin Name: Weekly Schedule
Plugin URI: http://yannickcorner.nayanna.biz/wordpress-plugins/
Description: A plugin used to create a page with a list of TV shows
Version: 2.2
Author: Yannick Lefebvre
Author URI: http://yannickcorner.nayanna.biz   
Copyright 2010  Yannick Lefebvre  (email : ylefebvre@gmail.com)    

This program is free software; you can redistribute it and/or modify   
it under the terms of the GNU General Public License as published by    
the Free Software Foundation; either version 2 of the License, or    
(at your option) any later version.    

This program is distributed in the hope that it will be useful,    
but WITHOUT ANY WARRANTY; without even the implied warranty of    
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    
GNU General Public License for more details.    

You should have received a copy of the GNU General Public License    
along with this program; if not, write to the Free Software    
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA*/

if (is_file(trailingslashit(ABSPATH.PLUGINDIR).'weekly-schedule.php')) {
	define('WS_FILE', trailingslashit(ABSPATH.PLUGINDIR).'weekly-schedule.php');
}
else if (is_file(trailingslashit(ABSPATH.PLUGINDIR).'weekly-schedule/weekly-schedule.php')) {
	define('WS_FILE', trailingslashit(ABSPATH.PLUGINDIR).'weekly-schedule/weekly-schedule.php');
}

function ws_install() {
	global $wpdb;

	$charset_collate = '';
	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if (!empty($wpdb->charset)) {
			$charset_collate .= " DEFAULT CHARACTER SET $wpdb->charset";
		}
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}
	
	$wpdb->wscategories = $wpdb->prefix.'wscategories';

	$result = $wpdb->query("
			CREATE TABLE IF NOT EXISTS `$wpdb->wscategories` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`scheduleid` int(10) default NULL,
				PRIMARY KEY  (`id`)
				) $charset_collate"); 
				
	$catsresult = $wpdb->query("
			SELECT * from `$wpdb->wscategories`");
			
	if (!$catsresult)
		$result = $wpdb->query("
			INSERT INTO `$wpdb->wscategories` (`name`, `scheduleid`) VALUES
			('Default', 1)");				
				
	$wpdb->wsdays = $wpdb->prefix.'wsdays';
	
	$result = $wpdb->query("
			CREATE TABLE IF NOT EXISTS `$wpdb->wsdays` (
				`id` int(10) unsigned NOT NULL,
				`name` varchar(12) NOT NULL,
				`rows` int(10) unsigned NOT NULL,
				`scheduleid` int(10) NOT NULL default '0',
				PRIMARY KEY  (`id`, `scheduleid`)
				)  $charset_collate"); 
				
	$daysresult = $wpdb->query("
			SELECT * from `$wpdb->wsdays`");
			
	if (!$daysresult)
		$result = $wpdb->query("
			INSERT INTO `$wpdb->wsdays` (`id`, `name`, `rows`, `scheduleid`) VALUES
			(1, 'Sun', 1, 1),
			(2, 'Mon', 1, 1),
			(3, 'Tue', 1, 1),
			(4, 'Wes', 1, 1),
			(5, 'Thu', 1, 1),
			(6, 'Fri', 1, 1),
			(7, 'Sat', 1, 1)");
			
	$wpdb->wsitems = $wpdb->prefix.'wsitems';
			
	$wpdb->query("
			CREATE TABLE IF NOT EXISTS `$wpdb->wsitems` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`description` text NOT NULL,
				`address` varchar(255) NOT NULL,
				`starttime` float unsigned NOT NULL,
				`duration` float NOT NULL,
				`row` int(10) unsigned NOT NULL,
				`day` int(10) unsigned NOT NULL,
				`category` int(10) unsigned NOT NULL,
				`scheduleid` int(10) NOT NULL default '0',
				PRIMARY KEY  (`id`,`scheduleid`)
			) $charset_collate");

	$upgradeoptions = get_option('WS_PP');
	
	if ($upgradeoptions != false)
	{
		if ($upgradeoptions['version'] != '2.0')
		{
			delete_option("WS_PP");
			
			$wpdb->query("ALTER TABLE `$wpdb->wscategories` ADD scheduleid int(10)");
			$wpdb->query("UPDATE `$wpdb->wscategories` set scheduleid = 1");
			
			$wpdb->query("ALTER TABLE `$wpdb->wsitems` ADD scheduleid int(10)");
			$wpdb->query("ALTER TABLE `$wpdb->wsitems` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL");
			$wpdb->query("ALTER TABLE `$wpdb->wsitems` DROP PRIMARY KEY");
			$wpdb->query("ALTER TABLE `$wpdb->wsitems` ADD PRIMARY KEY (id, scheduleid)");
			$wpdb->query("ALTER TABLE `$wpdb->wsitems` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT");			
			$wpdb->query("UPDATE `$wpdb->wsitems` set scheduleid = 1");
			
			$wpdb->query("ALTER TABLE `$wpdb->wsdays` ADD scheduleid int(10)");
			$wpdb->query("ALTER TABLE `$wpdb->wsdays` DROP PRIMARY KEY");
			$wpdb->query("ALTER TABLE `$wpdb->wsdays` ADD PRIMARY KEY (id, scheduleid)");
			$wpdb->query("UPDATE `$wpdb->wsdays` set scheduleid = 1");
		
			$upgradeoptions['adjusttooltipposition'] = true;
			$upgradeoptions['schedulename'] = "Default";
			
			update_option('WS_PP1',$upgradeoptions);
		
			$genoptions['stylesheet'] = $upgradeoptions['stylesheet'];
			$genoptions['numberschedules'] = 2;
			$genoptions['debugmode'] = false;
			$genoptions['includestylescript'] = $upgradeoptions['includestylescript'];
			$genoptions['version'] = "2.0";
		
			update_option('WeeklyScheduleGeneral', $genoptions);		
		}
	}		
	
	$options = get_option('WS_PP1');

	if ($options == false) {
		$options['starttime'] = 19;
		$options['endtime'] = 22;
		$options['timedivision'] = 0.5;
		$options['tooltipwidth'] = 300;
		$options['tooltiptarget'] = 'rightMiddle';
		$options['tooltippoint'] = 'leftMiddle';
		$options['tooltipcolorscheme'] = 'cream';
		$options['displaydescription'] = "tooltip";
		$options['daylist'] = "";
		$options['timeformat'] = "24hours";
		$options['layout'] = 'horizontal';
		$options['adjusttooltipposition'] = true;
		$options['schedulename'] = "Default";
		
		update_option('WS_PP1',$options);
	}
	
	$genoptions = get_option("WeeklyScheduleGeneral");
	
	if ($genoptions == false) {
		$genoptions['stylesheet'] = "stylesheet.css";
		$genoptions['numberschedules'] = 2;
		$genoptions['debugmode'] = false;
		$genoptions['includestylescript'] = "";
		$genoptions['version'] = "2.0";
		
		update_option("WeeklyScheduleGeneral", $genoptions);
	}

}
register_activation_hook(WS_FILE, 'ws_install');

if ( ! class_exists( 'WS_Admin' ) ) {
	class WS_Admin {		
		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_options_page('Weekly Schedule for Wordpress', 'Weekly Schedule', 9, basename(__FILE__), array('WS_Admin','config_page'));
				add_filter( 'plugin_action_links', array( 'WS_Admin', 'filter_plugin_actions'), 10, 2 );
							}
		} // end add_WS_config_page()

		function filter_plugin_actions( $links, $file ){
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
			if ( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=weekly-schedule.php">' . __('Settings') . '</a>';
				
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}

		function config_page() {
			global $dlextensions;
			global $wpdb;
			
			$adminpage == "";
			
			if ( !defined('WP_ADMIN_URL') )
				define( 'WP_ADMIN_URL', get_option('siteurl') . '/wp-admin');
			
			if ( isset($_GET['schedule']) ) {
				$schedule = $_GET['schedule'];				
			}
			elseif (isset($_POST['schedule'])) {
				$schedule = $_POST['schedule'];
			}
			else
			{
				$schedule = 1;
			}
			
			if ( isset($_GET['copy']))
			{
				$destination = $_GET['copy'];
				$source = $_GET['source'];
				
				$sourcesettingsname = 'WS_PP' . $source;
				$sourceoptions = get_option($sourcesettingsname);
				
				$destinationsettingsname = 'WS_PP' . $destination;
				update_option($destinationsettingsname, $sourceoptions);
				
				$schedule = $destination;
			}

			if ( isset($_GET['reset']) && $_GET['reset'] == "true") {
			
				$options['starttime'] = 19;
				$options['endtime'] = 22;
				$options['timedivision'] = 0.5;
				$options['tooltipwidth'] = 300;
				$options['tooltiptarget'] = 'rightMiddle';
				$options['tooltippoint'] = 'leftMiddle';
				$options['tooltipcolorscheme'] = 'cream';
				$options['displaydescription'] = "tooltip";
				$options['daylist'] = "";
				$options['timeformat'] = "24hours";
				$options['layout'] = 'horizontal';
				$options['adjusttooltipposition'] = true;
				$options['schedulename'] = "Default";
			
				$schedule = $_GET['reset'];
				$schedulename = 'WS_PP' . $schedule;
				
				update_option($schedulename, $options);
			}
			if ( isset($_GET['settings']))
			{
				if ($_GET['settings'] == 'categories')
				{
					$adminpage = 'categories';
				}
				elseif ($_GET['settings'] == 'items')
				{
					$adminpage = 'items';
				}
				elseif ($_GET['settings'] == 'general')
				{
					$adminpage = 'general';
				}
				elseif ($_GET['settings'] == 'days')
				{
					$adminpage = 'days';
				}
			
			}
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Weekly Schedule for WordPress options.'));
				check_admin_referer('wspp-config');
				
				if ($_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "1.0")
				{
					$itemsquarterhour = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE MOD(duration, 1) = 0.25");
					$itemshalfhour = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE MOD(duration, 1) = 0.5");
					
					if ($itemsquarterhour)
					{
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					}
					elseif ($itemshalfhour)
					{
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have half-hourly durations</strong></div>';
						$options['timedivision'] = "0.5";
					}
					else
						$options['timedivision'] = $_POST['timedivision'];					
				}
				elseif ($_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "0.5")
				{
					$itemsquarterhour = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE MOD(duration, 1) = 0.25");
					
					if ($itemsquarterhour)
					{
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					}
					else
						$options['timedivision'] = $_POST['timedivision'];				
				}
				else
					$options['timedivision'] = $_POST['timedivision'];

				foreach (array('starttime','endtime','tooltipwidth','tooltiptarget','tooltippoint','tooltipcolorscheme',
						'displaydescription','daylist', 'timeformat', 'layout', 'schedulename') as $option_name) {
						if (isset($_POST[$option_name])) {
							$options[$option_name] = $_POST[$option_name];
						}
					}
					
				foreach (array('adjusttooltipposition') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}

				
				$schedulename = 'WS_PP' . $schedule;
				update_option($schedulename, $options);
				
				echo '<div id="message" class="updated fade"><p><strong>Weekly Schedule: Schedule ' . $schedule . ' Updated</strong></div>';
			}
			if (isset($_POST['submitgen']))
			{
				if (!current_user_can('manage_options')) die(__('You cannot edit the Weekly Schedule for WordPress options.'));
				check_admin_referer('wspp-config');
				
				foreach (array('stylesheet', 'numberschedules') as $option_name) {
					if (isset($_POST[$option_name])) {
						$genoptions[$option_name] = $_POST[$option_name];
					}
				}
				
				foreach (array('debugmode') as $option_name) {
					if (isset($_POST[$option_name])) {
						$genoptions[$option_name] = true;
					} else {
						$genoptions[$option_name] = false;
					}
				}
				
				update_option('WeeklyScheduleGeneral', $genoptions);				
			}
			if ( isset($_GET['editcat']))
			{					
				$adminpage = 'categories';
				
				$mode = "edit";
								
				$selectedcat = $wpdb->get_row("select * from " . $wpdb->prefix . "wscategories where id = " . $_GET['editcat']);
			}			
			if ( isset($_POST['newcat']) || isset($_POST['updatecat'])) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Weekly Schedule for WordPress options.'));
				check_admin_referer('wspp-config');
				
				if (isset($_POST['name']))
					$newcat = array("name" => $_POST['name'], "scheduleid" => $_POST['schedule']);
				else
					$newcat = "";
					
				if (isset($_POST['id']))
					$id = array("id" => $_POST['id']);
					
					
				if (isset($_POST['newcat']))
				{
					$wpdb->insert( $wpdb->prefix.'wscategories', $newcat);
					echo '<div id="message" class="updated fade"><p><strong>Inserted New Category</strong></div>';
				}
				elseif (isset($_POST['updatecat']))
				{
					$wpdb->update( $wpdb->prefix.'wscategories', $newcat, $id);
					echo '<div id="message" class="updated fade"><p><strong>Category Updated</strong></div>';
				}
				
				$mode = "";
				
				$adminpage = 'categories';	
			}
			if (isset($_GET['deletecat']))
			{
				$adminpage = 'categories';
				
				$catexist = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wscategories WHERE id = " . $_GET['deletecat']);
				
				if ($catexist)
				{
					$wpdb->query("DELETE from " . $wpdb->prefix . "wscategories WHERE id = " . $_GET['deletecat']);
					echo '<div id="message" class="updated fade"><p><strong>Category Deleted</strong></div>';
				}
			}
			if ( isset($_GET['edititem']))
			{					
				$adminpage = 'items';
				
				$mode = "edit";
								
				$selecteditem = $wpdb->get_row("select * from " . $wpdb->prefix . "wsitems where id = " . $_GET['edititem'] . " AND scheduleid = " . $_GET['schedule']);
			}
			if (isset($_POST['newitem']) || isset($_POST['updateitem']))
			{
			// Need to re-work all of this to support multiple schedules 
				if (!current_user_can('manage_options')) die(__('You cannot edit the Weekly Schedule for WordPress options.'));
				check_admin_referer('wspp-config');
				
				if (isset($_POST['name']) && isset($_POST['starttime']) && isset($_POST['duration']) && $_POST['name'] != '')
				{
					$newitem = array("name" => $_POST['name'],
									 "description" => $_POST['description'],
									 "address" => $_POST['address'],
									 "starttime" => $_POST['starttime'],
									 "category" => $_POST['category'],
									 "duration" => $_POST['duration'],
									 "day" => $_POST['day'],
									 "scheduleid" => $_POST['schedule']);
									 
					if (isset($_POST['updateitem']))
					{
						$origrow = $_POST['oldrow'];
						$origday = $_POST['oldday'];
					}

					$rowsearch = 1;
					$row = 1;
					
					while ($rowsearch == 1)
					{
						if ($_POST['id'] != "")
							$checkid = " and id <> " . $_POST['id'];
						else
							$checkid = "";
							
						$endtime = $newitem['starttime'] + $newitem['duration'];
					
						$conflictquery = "SELECT * from " . $wpdb->prefix . "wsitems where day = " . $newitem['day'] . $checkid;
						$conflictquery .= " and row = " . $row;
						$conflictquery .= " and scheduleid = " . $newitem['scheduleid'];
						$conflictquery .= " and ((" . $newitem['starttime'] . " < starttime and " . $endtime . " > starttime) or";
						$conflictquery .= "      (" . $newitem['starttime'] . " >= starttime and " . $newitem['starttime'] . " < starttime + duration)) ";
						
						$conflictingitems = $wpdb->get_results($conflictquery);
						
						if ($conflictingitems)
						{
							$row++;
						}
						else
						{
							$rowsearch = 0;
						}
					}
					
					if (isset($_POST['updateitem']))
					{
						if ($origrow != $row || $origday != $_POST['day'])
						{
							if ($origrow > 1)
							{
								$itemday = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays WHERE id = " . $origday . " AND scheduleid = " . $_POST['schedule']);
								
								$othersonrow = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE day = " . $origday . " AND row = " . $origrow . " AND scheduleid = " . $_POST['schedule'] . " AND id != " . $_POST['id']);
								if (!$othersonrow)
								{
									if ($origrow != $itemday->rows)
									{
										for ($i = $origrow + 1; $i <= $itemday->rows; $i++)
										{
											$newrow = $i - 1;
											$changerow = array("row" => $newrow);
											$oldrow = array("row" => $i, "day" => $origday);
											$wpdb->update($wpdb->prefix . 'wsitems', $changerow, $oldrow);
										}
									}
									
									$dayid = array("id" => $itemday->id, "scheduleid" => $_POST['schedule']);
									$newrow = $itemday->rows - 1;
									$newdayrow = array("rows" => $newrow);
									
									$wpdb->update($wpdb->prefix . 'wsdays', $newdayrow, $dayid);
								}
							}							
						}
					}
					
					$dayrow = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays where id = " . $_POST['day'] . " AND scheduleid = " . $_POST['schedule']);
					if ($dayrow->rows < $row)
					{
						$dayid = array("id" => $_POST['day'], "scheduleid" => $_POST['schedule']);
						$newdayrow = array("rows" => $row);
						
						$wpdb->update($wpdb->prefix . 'wsdays', $newdayrow, $dayid);
					}
					
					$newitem['row'] = $row;
						
					if (isset($_POST['id']))
						$id = array("id" => $_POST['id'], "scheduleid" => $_POST['schedule']);
						
					if (isset($_POST['newitem']))
					{
						$wpdb->insert( $wpdb->prefix.'wsitems', $newitem);
						echo '<div id="message" class="updated fade"><p><strong>Inserted New Item</strong></div>';
					}
					elseif (isset($_POST['updateitem']))
					{
						$wpdb->update( $wpdb->prefix.'wsitems', $newitem, $id);
						echo '<div id="message" class="updated fade"><p><strong>Item Updated</strong></div>';
					}									 
				}				
				
				$mode = "";
					
				$adminpage = 'items';
			}
			if (isset($_GET['deleteitem']))
			{
				$adminpage = 'items';
				
				$itemexist = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsitems WHERE id = " . $_GET['deleteitem'] . " AND scheduleid = " . $_GET['schedule']);
				$itemday = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays WHERE id = " . $itemexist->day . " AND scheduleid = " . $_GET['schedule']);
				
				if ($itemexist)
				{
					$wpdb->query("DELETE from " . $wpdb->prefix . "wsitems WHERE id = " . $_GET['deleteitem'] . " AND scheduleid = " . $_GET['schedule']);
					
					if ($itemday->rows > 1)
					{						
						$othersonrow = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE day = " . $itemexist->day . " AND scheduleid = " . $_GET['schedule'] . " AND row = " . $itemexist->row);
						if (!$othersonrow)
						{
							if ($itemexist->row != $itemday->rows)
							{
								for ($i = $itemexist->row + 1; $i <= $itemday->rows; $i++)
								{
									$newrow = $i - 1;
									$changerow = array("row" => $newrow);
									$oldrow = array("row" => $i, "day" => $itemday->id);
									$wpdb->update($wpdb->prefix . 'wsitems', $changerow, $oldrow);
								}
							}
							
							$dayid = array("id" => $itemexist->day, "scheduleid" => $_GET['schedule']);
							$newrow = $itemday->rows - 1;
							$newdayrow = array("rows" => $newrow);
							
							$wpdb->update($wpdb->prefix . 'wsdays', $newdayrow, $dayid);
						}
					}	
					echo '<div id="message" class="updated fade"><p><strong>Item Deleted</strong></div>';
				}				
			}
			if (isset($_POST['updatedays']))
			{
				$dayids = array(1, 2, 3, 4, 5, 6, 7);
				
				foreach($dayids as $dayid)
				{
					$daynamearray = array("name" => $_POST[$dayid]);
					$dayidarray = array("id" => $dayid, "scheduleid" => $_POST['schedule']);
					
					$wpdb->update($wpdb->prefix . 'wsdays', $daynamearray, $dayidarray);
				}					
			}
			
			$wspluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';
	
			if ($schedule == '')
			{
				$options = get_option('WS_PP1');
				if ($options == false)
				{
					$oldoptions = get_option('WS_PP');
					if ($options)
						echo "If you are upgrading from versions before 2.0, please deactivate and reactivate the plugin in the Wordpress Plugins admin to upgrade all tables correctly.";
				}
					
				$schedule = 1;
			}
			else
			{
				$settingsname = 'WS_PP' . $schedule;
				$options = get_option($settingsname);
			}

			if ($options == "")
			{
				$options['starttime'] = 19;
				$options['endtime'] = 22;
				$options['timedivision'] = 0.5;
				$options['tooltipwidth'] = 300;
				$options['tooltiptarget'] = 'rightMiddle';
				$options['tooltippoint'] = 'leftMiddle';
				$options['tooltipcolorscheme'] = 'cream';
				$options['displaydescription'] = "tooltip";
				$options['daylist'] = "";
				$options['timeformat'] = "24hours";
				$options['layout'] = 'horizontal';
				$options['adjusttooltipposition'] = true;
				$options['schedulename'] = "Default";
			
				$schedulename = 'WS_PP' . $schedule;
				
				update_option($schedulename, $options);
				
				$catsresult = $wpdb->query("SELECT * from " . $wpdb->prefix . "wscategories where scheduleid = " . $schedule);
						
				if (!$catsresult)
				{
					$sqlstatement = "INSERT INTO " . $wpdb->prefix . "wscategories (`name`, `scheduleid`) VALUES 
									('Default', " . $schedule . ")";
					$result = $wpdb->query($sqlstatement);
				}

				$wpdb->wsdays = $wpdb->prefix.'wsdays';
										
				$daysresult = $wpdb->query("SELECT * from " . $wpdb->prefix . "wsdays where scheduleid = " . $schedule);
						
				if (!$daysresult)
				{
					$sqlstatement = "INSERT INTO " . $wpdb->prefix . "wsdays (`id`, `name`, `rows`, `scheduleid`) VALUES
									(1, 'Sun', 1, " . $schedule . "),
									(2, 'Mon', 1, " . $schedule . "),
									(3, 'Tue', 1, " . $schedule . "),
									(4, 'Wes', 1, " . $schedule . "),
									(5, 'Thu', 1, " . $schedule . "),
									(6, 'Fri', 1, " . $schedule . "),
									(7, 'Sat', 1, " . $schedule . ")";
					$result = $wpdb->query($sqlstatement);
				}
			}
			
			$genoptions = get_option('WeeklyScheduleGeneral');
			
			if ($genoptions == "")
			{			
				$genoptions['stylesheet'] = $upgradeoptions['stylesheet'];
				$genoptions['numberschedules'] = 2;
				$genoptions['debugmode'] = false;
				$genoptions['includestylescript'] = $upgradeoptions['includestylescript'];
				$genoptions['version'] = "2.0";
		
				update_option('WeeklyScheduleGeneral', $genoptions);	
			}
			
			?>
			<div class="wrap">
				<h2>Weekly Schedule Configuration</h2>
				<a href="http://yannickcorner.nayanna.biz/wordpress-plugins/weekly-schedule/" target="weeklyschedule"><img src="<?php echo $wspluginpath; ?>/icons/btn_donate_LG.gif" /></a> | <a target='wsinstructions' href='http://wordpress.org/extend/plugins/weekly-schedule/installation/'>Installation Instructions</a> | <a href='http://wordpress.org/extend/plugins/weekly-schedule/faq/' target='llfaq'>FAQ</a> | <a href='http://yannickcorner.nayanna.biz/contact-me'>Contact the Author</a><br /><br />
				
				<form name='wsadmingenform' action="<?php echo WP_ADMIN_URL ?>/options-general.php?page=weekly-schedule.php" method="post" id="ws-conf">
				<?php
				if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
				<fieldset style='border:1px solid #CCC;padding:10px'>
				<legend class="tooltip" title='These apply to all schedules' style='padding: 0 5px 0 5px;'><strong>General Settings <span style="border:0;padding-left: 15px;" class="submit"><input type="submit" name="submitgen" value="Update General Settings &raquo;" /></span></strong></legend>
				<table>
				<tr>
				<td style='width:200px'>Stylesheet File Name</td>
				<td><input type="text" id="stylesheet" name="stylesheet" size="40" value="<?php echo $genoptions['stylesheet']; ?>"/></td>
				<td style='padding-left: 10px;padding-right:10px'>Number of Schedules</td>
				<td><input type="text" id="numberschedules" name="numberschedules" size="5" value="<?php if ($genoptions['numberschedules'] == '') echo '2'; echo $genoptions['numberschedules']; ?>"/></td>
				</tr>
				<tr>
				<td style="padding-left: 10px;padding-right:10px">Debug Mode</td>
				<td><input type="checkbox" id="debugmode" name="debugmode" <?php if ($genoptions['debugmode']) echo ' checked="checked" '; ?>/></td>
				</tr>
				<tr>
					<td colspan="2">Additional pages to load styles and scripts (Comma-Separated List of Page IDs)</td>
					<td colspan="2"><input type='text' name='includestylescript' style='width: 200px' value='<?php echo $genoptions['includestylescript']; ?>' /></td>
				</tr>
				</table>
				</fieldset>
				</form>

				<div style='padding-top: 15px;clear:both'>
					<fieldset style='border:1px solid #CCC;padding:10px'>
					<legend style='padding: 0 5px 0 5px;'><strong>Schedule Selection and Usage Instructions</strong></legend>				
						<FORM name="scheduleselection">
							Select Current Style Set: 
							<SELECT name="schedulelist" style='width: 300px'>
							<?php if ($genoptions['numberschedules'] == '') $numberofschedules = 2; else $numberofschedules = $genoptions['numberschedules'];
								for ($counter = 1; $counter <= $numberofschedules; $counter++): ?>
									<?php $tempoptionname = "WS_PP" . $counter;
									   $tempoptions = get_option($tempoptionname); ?>
									   <option value="<?php echo $counter ?>" <?php if ($schedule == $counter) echo 'SELECTED';?>>Schedule <?php echo $counter ?><?php if ($tempoptions != "") echo " (" . $tempoptions['schedulename'] . ")"; ?></option>
								<?php endfor; ?>
							</SELECT>
							<INPUT type="button" name="go" value="Go!" onClick="window.location= '?page=weekly-schedule.php&amp;settings=<?php echo $adminpage; ?>&amp;schedule=' + document.scheduleselection.schedulelist.options[document.scheduleselection.schedulelist.selectedIndex].value">						
							Copy from: 
							<SELECT name="copysource" style='width: 300px'>
							<?php if ($genoptions['numberschedules'] == '') $numberofschedules = 2; else $numberofschedules = $genoptions['numberschedules'];
								for ($counter = 1; $counter <= $numberofschedules; $counter++): ?>
									<?php $tempoptionname = "WS_PP" . $counter;
									   $tempoptions = get_option($tempoptionname); 
									   if ($counter != $schedule):?>
									   <option value="<?php echo $counter ?>" <?php if ($schedule == $counter) echo 'SELECTED';?>>Schedule <?php echo $counter ?><?php if ($tempoptions != "") echo " (" . $tempoptions['schedulename'] . ")"; ?></option>
									   <?php endif; 
								    endfor; ?>
							</SELECT>
							<INPUT type="button" name="copy" value="Copy!" onClick="window.location= '?page=weekly-schedule.php&amp;copy=<?php echo $schedule; ?>&source=' + document.scheduleselection.copysource.options[document.scheduleselection.copysource.selectedIndex].value">							
					<br />
					<br />
					<table class='widefat' style='clear:none;width:100%;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
						<thead>
						<tr>
							<th style='width:80px' class="tooltip">
								Schedule #
							</th>
							<th style='width:130px' class="tooltip">
								Schedule Name
							</th>
							<th class="tooltip">
								Code to insert on a Wordpress page to see Weekly Schedule
							</th>
						</tr>
						</thead>
						<tr>
						<td style='background: #FFF'><?php echo $schedule; ?></td><td style='background: #FFF'><?php echo $options['schedulename']; ?></a></td><td style='background: #FFF'><?php echo "[weekly-schedule schedule=" . $schedule . "]"; ?></td><td style='background: #FFF;text-align:center'></td>
						</tr>
					</table> 
					<br />
					</FORM>
					</fieldset>
				</div>
				<br />

	
				<fieldset style='border:1px solid #CCC;padding:10px'>
				<legend style='padding: 0 5px 0 5px;'><strong>Settings for Schedule <?php echo $schedule; ?> - <?php echo $options['schedulename']; ?></strong></legend>	
				<?php if (($adminpage == "") || ($adminpage == "general")): ?>
				<a href="?page=weekly-schedule.php&amp;settings=general&amp;schedule=<?php echo $schedule; ?>"><strong>General Settings</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days&amp;schedule=<?php echo $schedule; ?>">Manage Days Labels</a><br /><br />
				<form name="wsadminform" action="<?php echo WP_ADMIN_URL ?>/options-general.php?page=weekly-schedule.php" method="post" id="ws-config">
				<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					Schedule Name: <input type="text" id="schedulename" name="schedulename" size="80" value="<?php echo $options['schedulename']; ?>"/><br /><br />
					<strong>Time-related Settings</strong><br />
					<input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
					<table>
					<tr>
					<td>Schedule Layout</td>
					<td><select style="width: 200px" name='layout'>
					<?php $layouts = array("horizontal" => "Horizontal", "vertical" => "Vertical");
						foreach($layouts as $key => $layout)
						{
							if ($key == $options['layout'])
								$samedesc = "selected='selected'";
							else
								$samedesc = "";
								
							echo "<option value='" . $key . "' " . $samedesc . ">" . $layout . "\n";
						}
					?>
					</select></td>
					<td>Time Display Format</td>
					<td><select style="width: 200px" name='timeformat'>
					<?php $descriptions = array("24hours" => "24 Hours (e.g. 17h30)", "12hours" => "12 Hours (e.g. 1:30pm)");
						foreach($descriptions as $key => $description)
						{
							if ($key == $options['timeformat'])
								$samedesc = "selected='selected'";
							else
								$samedesc = "";
								
							echo "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
						}
					?>
					</select></td>
					</tr>
					<tr>
					<td>Start Time</td>
					<td><select style='width: 200px' name="starttime">
					<?php $maxtime = 24 + $options['timedivision']; for ($i = 0; $i < $maxtime; $i+= $options['timedivision'])
						  {
								if ($options['timeformat'] == '24hours')
									$hour = floor($i);
								elseif ($options['timeformat'] == '12hours')
								{
									if ($i < 12)
									{
										$timeperiod = "am";
										if ($i == 0)
											$hour = 12;
										else
											$hour = floor($i);
									}
									else
									{
										$timeperiod = "pm";
										if ($i >= 12 && $i < 13)
											$hour = floor($i);
										else
											$hour = floor($i) - 12;
									}
								}
							
								if (fmod($i, 1) == 0.25)
                                    $minutes = "15";
								elseif (fmod($i, 1) == 0.50)
									$minutes = "30";
								elseif (fmod($i, 1) == 0.75)
									$minutes = "45";
                                else
                                    $minutes = "00";

									
								if ($i == $options['starttime']) 
									$selectedstring = "selected='selected'";
								else
									$selectedstring = "";
									
								if ($options['timeformat'] == '24 hours')
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . "h" . $minutes . "\n";
								else
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . ":" . $minutes . $timeperiod . "\n";
						  }
					?>
					</select></td>
					<td>End Time</td>
					<td><select style='width: 200px' name="endtime">
					<?php for ($i = 0; $i < $maxtime; $i+= $options['timedivision'])
						  {
						  		if ($options['timeformat'] == '24hours')
									$hour = floor($i);
								elseif ($options['timeformat'] == '12hours')
								{
									if ($i < 12)
									{
										$timeperiod = "am";
										if ($i == 0)
											$hour = 12;
										else
											$hour = floor($i);
									}
									else
									{
										$timeperiod = "pm";
										if ($i >= 12 && $i < 13)
											$hour = floor($i);
										else
											$hour = floor($i) - 12;
									}
								}
								
								if (fmod($i, 1) == 0.25)
                                    $minutes = "15";
								elseif (fmod($i, 1) == 0.50)
									$minutes = "30";
								elseif (fmod($i, 1) == 0.75)
									$minutes = "45";
                                else
                                    $minutes = "00";

									
								
								if ($i == $options['endtime']) 
									$selectedstring = "selected='selected'";
								else
									$selectedstring = "";

								if ($options['timeformat'] == '24 hours')
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . "h" . $minutes . "\n";
								else
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . ":" . $minutes . $timeperiod . "\n";
						  }
					?>
					</select></td>
					</tr>
					<tr>
					<td>Cell Time Division</td>
					<td><select style='width: 250px' name='timedivision'>
					<?php $timedivisions = array("0.25" => "Quarter-Hourly (15 min intervals)", ".50" => "Half-Hourly (30 min intervals)", "1.0" => "Hourly (60 min intervals)");
						foreach($timedivisions as $key => $timedivision)
						{
							if ($key == $options['timedivision'])
								$sametime = "selected='selected'";
							else
								$sametime = "";
								
							echo "<option value='" . $key . "' " . $sametime . ">" . $timedivision . "\n";
						}
					?>	
					</select></td>
					<td>Show Description</td>
					<td><select style="width: 200px" name='displaydescription'>
					<?php $descriptions = array("tooltip" => "Show as tooltip", "cell" => "Show in cell after item name", "none" => "Do not display");
						foreach($descriptions as $key => $description)
						{
							if ($key == $options['displaydescription'])
								$samedesc = "selected='selected'";
							else
								$samedesc = "";
								
							echo "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
						}
					?>
					</select></td></tr>
					<tr>
						<td colspan='2'>Day List (comma-separated Day IDs to specify days to be displayed and their order)
						</td>
						<td colspan='2'><input type='text' name='daylist' style='width: 200px' value='<?php echo $options['daylist']; ?>' />
						</td>						
					</tr>
					</table>
					<br /><br />
					<strong>Tooltip Configuration</strong>
					<table>
					<tr>
					<td>Tooltip Color Scheme</td>
					<td><select name='tooltipcolorscheme' style='width: 100px'>
						<?php $colors = array('cream', 'dark', 'green', 'light', 'red', 'blue');
							  foreach ($colors as $color)
								{
									if ($color == $options['tooltipcolorscheme'])
										$samecolor = "selected='selected'";
									else
										$samecolor = "";
										
									echo "<option value='" . $color . "' " . $samecolor . ">" . $color . "\n";
								}
						?>						
					</select></td>
					<td>Tooltip Width</td><td><input type='text' name='tooltipwidth' style='width: 100px' value='<?php echo $options['tooltipwidth']; ?>' /></td>
					</tr>
					<tr>
					<td>Tooltip Anchor Point on Data Cell</td>
					<td><select name='tooltiptarget' style='width: 200px'>
						<?php $positions = array('topLeft' => 'Top-Left Corner', 'topMiddle' => 'Middle of Top Side', 
												'topRight' => 'Top-Right Corner', 'rightTop' => 'Right Side of Top-Right Corner',
												'rightMiddle' => 'Middle of Right Side', 'rightBottom' => 'Right Side of Bottom-Right Corner',
												'bottomLeft' => 'Under Bottom-Left Side', 'bottomMiddle' => 'Under Middle of Bottom Side',
												'bottomRight' => 'Under Bottom-Right Side', 'leftTop' => 'Left Side of Top-Left Corner',
												'leftMiddle' => 'Middle of Left Side', 'leftBottom' => 'Left Side of Bottom-Left Corner');
								foreach($positions as $index => $position)
								{
									if ($index == $options['tooltiptarget'])
										$sameposition = "selected='selected'";
									else
										$sameposition = "";
										
									echo "<option value='" . $index . "' " . $sameposition . ">" . $position . "\n";
								}
												
						?>
					</select></td>
					<td>Tooltip Attachment Point</td>
					<td><select name='tooltippoint' style='width: 200px'>
						<?php $positions = array('topLeft' => 'Top-Left Corner', 'topMiddle' => 'Middle of Top Side', 
												'topRight' => 'Top-Right Corner', 'rightTop' => 'Right Side of Top-Right Corner',
												'rightMiddle' => 'Middle of Right Side', 'rightBottom' => 'Bottom-Right Corner',
												'bottomLeft' => 'Bottom-Left Corner', 'bottomMiddle' => 'Center of Bottom Side',
												'bottomRight' => 'Bottom Corner of Right Side', 'leftTop' => 'Top Corner of Left Side',
												'leftMiddle' => 'Middle of Left Side', 'leftBottom' => 'Bottom Corner of Left Side');
								foreach($positions as $index => $position)
								{
									if ($index == $options['tooltippoint'])
										$sameposition = "selected='selected'";
									else
										$sameposition = "";
										
									echo "<option value='" . $index . "' " . $sameposition . ">" . $position . "\n";
								}
												
						?>
					</select></td>
					</tr>
					<tr>
					<td>Auto-Adjust Position to be visible</td>
					<td><input type="checkbox" id="adjusttooltipposition" name="adjusttooltipposition" <?php if ($options['adjusttooltipposition'] == true) echo ' checked="checked" '; ?>/></td>
					<td></td><td></td>
					</tr>
					</table>
					<p style="border:0;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
					</form>
					</fieldset>
				<?php /* --------------------------------------- Categories --------------------------------- */ ?>
				<?php elseif ($adminpage == "categories"): ?>
				<a href="?page=weekly-schedule.php&amp;settings=general&amp;schedule=<?php echo $schedule; ?>">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>"><strong>Manage Schedule Categories</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=items&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days&amp;schedule=<?php echo $schedule; ?>">Manage Days Labels</a><br /><br />
				<div style='float:left;margin-right: 15px'>
					<form name="wscatform" action="" method="post" id="ws-config">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					<?php if ($mode == "edit"): ?>
					<strong>Editing Category #<?php echo $selectedcat->id; ?></strong><br />
					<?php endif; ?>
					Category Name: <input style="width:300px" type="text" name="name" <?php if ($mode == "edit") echo "value='" . $selectedcat->name . "'";?>/>
					<input type="hidden" name="id" value="<?php if ($mode == "edit") echo $selectedcat->id; ?>" />
					<input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
					<?php if ($mode == "edit"): ?>
						<p style="border:0;" class="submit"><input type="submit" name="updatecat" value="Update &raquo;" /></p>
					<?php else: ?>
						<p style="border:0;" class="submit"><input type="submit" name="newcat" value="Insert New Category &raquo;" /></p>
					<?php endif; ?>
					</form>
				</div>
				<div>
					<?php $cats = $wpdb->get_results("SELECT count( i.id ) AS nbitems, c.name, c.id, c.scheduleid FROM " . $wpdb->prefix . "wscategories c LEFT JOIN " . $wpdb->prefix . "wsitems i ON i.category = c.id WHERE c.scheduleid = " . $schedule . " GROUP BY c.name");
					
							if ($cats): ?>
							  <table class='widefat' style='clear:none;width:400px;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
							  <thead>
							  <tr>
  							  <th scope='col' style='width: 50px' id='id' class='manage-column column-id' >ID</th>
							  <th scope='col' id='name' class='manage-column column-name' style=''>Name</th>
							  <th scope='col' style='width: 50px;text-align: right' id='items' class='manage-column column-items' style=''>Items</th>
							  <th style='width: 30px'></th>
							  </tr>
							  </thead>
							  
							  <tbody id='the-list' class='list:link-cat'>

							  <?php foreach($cats as $cat): ?>
								<tr>
								<td class='name column-name' style='background: #FFF'><?php echo $cat->id; ?></td>
								<td style='background: #FFF'><a href='?page=weekly-schedule.php&amp;editcat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>'><strong><?php echo $cat->name; ?></strong></a></td>
								<td style='background: #FFF;text-align:right'><?php echo $cat->nbitems; ?></td>
								<?php if ($cat->nbitems == 0): ?>
								<td style='background:#FFF'><a href='?page=weekly-schedule.php&amp;deletecat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>' 
								<?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to delete this category '%s'\n  'Cancel' to stop, 'OK' to delete."), $cat->name )) . "') ) { return true;}return false;\"" ?>><img src='<?php echo $wspluginpath; ?>/icons/delete.png' /></a></td>
								<?php else: ?>
								<td style='background: #FFF'></td>
								<?php endif; ?>
								</tr>
							  <?php endforeach; ?>				
							  
							  </tbody>
							  </table>
							 
							<?php endif; ?>
							
							<p>Categories can only be deleted when they don't have any associated items.</p>
				</div>
				<?php /* --------------------------------------- Items --------------------------------- */ ?>
				<?php elseif ($adminpage == "items"): ?>
				<a href="?page=weekly-schedule.php&amp;settings=general&amp;schedule=<?php echo $schedule; ?>">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items&amp;schedule=<?php echo $schedule; ?>"><strong>Manage Schedule Items</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=days&amp;schedule=<?php echo $schedule; ?>">Manage Days Labels</a><br /><br />
				<div style='float:left;margin-right: 15px;width: 500px;'>
					<form name="wsitemsform" action="" method="post" id="ws-config">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					
					<input type="hidden" name="id" value="<?php if ($mode == "edit") echo $selecteditem->id; ?>" />
					<input type="hidden" name="oldrow" value="<?php if ($mode == "edit") echo $selecteditem->row; ?>" />
					<input type="hidden" name="oldday" value="<?php if ($mode == "edit") echo $selecteditem->day; ?>" />
					<input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
					<?php if ($mode == "edit"): ?>
					<strong>Editing Item #<?php echo $selecteditem->id; ?></strong>
					<?php endif; ?>

					<table>
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					<tr>
					<td style='width: 180px'>Item Title</td>
					<td><input style="width:360px" type="text" name="name" <?php if ($mode == "edit") echo "value='" . $selecteditem->name . "'";?>/></td>
					</tr>
					<tr>
					<td>Category</td>
					<td><select style='width: 360px' name="category">
					<?php $cats = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "wscategories where scheduleid = " . $schedule . " ORDER by name");
					
						foreach ($cats as $cat)
						{
							if ($cat->id == $selecteditem->category)
									$selectedstring = "selected='selected'";
								else 
									$selectedstring = ""; 
									
							echo "<option value='" . $cat->id . "' " . $selectedstring . ">" .  $cat->name . "\n";
						}
					?></select></td>
					</tr>
					<tr>
					<td>Description</td>
					<td><textarea id="description" rows="5" cols="45" name="description"><?php if ($mode == "edit") echo  stripslashes($selecteditem->description);?></textarea></td>
					</tr>
					<tr>
					<td>Web Address</td>
					<td><input style="width:360px" type="text" name="address" <?php if ($mode == "edit") echo "value='" . $selecteditem->address . "'";?>/></td>
					</tr>
					<tr>
					<td>Day</td><td><select style='width: 360px' name="day">
					<?php $days = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "wsdays where scheduleid = " . $schedule . " ORDER by id");
					
						foreach ($days as $day)
						{
						
							if ($day->id == $selecteditem->day)
									$selectedstring = "selected='selected'";
								else 
									$selectedstring = ""; 
									
							echo "<option value='" . $day->id . "' " . $selectedstring . ">" .  $day->name . "\n";
						}
					?></select></td>
					</tr>
					<tr>
					<td>Start Time</td>
					<td><select style='width: 360px' name="starttime">
					<?php for ($i = $options['starttime']; $i < $options['endtime']; $i+= $options['timedivision'])
						  {
						  		if ($options['timeformat'] == '24hours')
									$hour = floor($i);
								elseif ($options['timeformat'] == '12hours')
								{
									if ($i < 12)
									{
										$timeperiod = "am";
										if ($i == 0)
											$hour = 12;
										else
											$hour = floor($i);
									}
									else
									{
										$timeperiod = "pm";
										if ($i >= 12 && $i < 13)
											$hour = floor($i);
										else
											$hour = floor($i) - 12;
									}
								}
									
								
								if (fmod($i, 1) == 0.25)
                                    $minutes = "15";
								elseif (fmod($i, 1) == 0.50)
									$minutes = "30";
								elseif (fmod($i, 1) == 0.75)
									$minutes = "45";
                                else
                                    $minutes = "00";
									
 								if ($i == $selecteditem->starttime)
									$selectedstring = "selected='selected'";
								else 
									$selectedstring = ""; 

								if ($options['timeformat'] == '24 hours')
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . "h" . $minutes . "\n";
								else
									echo "<option value='" . $i . "'" . $selectedstring . ">" .  $hour . ":" . $minutes . $timeperiod . "\n";
						  }
					?></select></td>
					</tr>
					<tr>
					<td>Duration</td>
					<td><select style='width: 360px' name="duration">
					<?php for ($i = $options['timedivision']; $i <= ($options['endtime'] - $options['starttime']); $i += $options['timedivision'])
						  {
								if (fmod($i, 1) == 0.25)
                                    $minutes = "15";
								elseif (fmod($i, 1) == 0.50)
									$minutes = "30";
								elseif (fmod($i, 1) == 0.75)
									$minutes = "45";
                                else
                                    $minutes = "00";
									
 								if ($i == $selecteditem->duration) 
									$selectedstring = "selected='selected'";
								else 
									$selectedstring = "";

								echo "<option value='" . $i . "' " . $selectedstring . ">" .  floor($i) . "h" . $minutes . "\n";
						  }
					?></select></td>
					</tr>
					</table>
					<?php if ($mode == "edit"): ?>
						<p style="border:0;" class="submit"><input type="submit" name="updateitem" value="Update &raquo;" /></p>
					<?php else: ?>
						<p style="border:0;" class="submit"><input type="submit" name="newitem" value="Insert New Item &raquo;" /></p>
					<?php endif; ?>
				</form>
				</div>
				<div>
				<?php $items = $wpdb->get_results("SELECT d.name as dayname, i.id, i.name, i.day, i.starttime FROM " . $wpdb->prefix . "wsitems as i, " . $wpdb->prefix . "wsdays as d WHERE i.day = d.id 
								and i.scheduleid = " . $schedule . " and d.scheduleid = " . $_GET['schedule'] . " ORDER by day, starttime, name");
					
							if ($items): ?>
							  <table class='widefat' style='clear:none;width:500px;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
							  <thead>
							  <tr>
  							  <th scope='col' style='width: 50px' id='id' class='manage-column column-id' >ID</th>
							  <th scope='col' id='name' class='manage-column column-name' style=''>Name</th>
							  <th scope='col' id='day' class='manage-column column-day' style='text-align: right'>Day</th>
							  <th scope='col' style='width: 50px;text-align: right' id='starttime' class='manage-column column-items' style=''>Start Time</th>
							  <th style='width: 30px'></th>
							  </tr>
							  </thead>
							  
							  <tbody id='the-list' class='list:link-cat'>

							  <?php foreach($items as $item): ?>
								<tr>
								<td class='name column-name' style='background: #FFF'><?php echo $item->id; ?></td>
								<td style='background: #FFF'><a href='?page=weekly-schedule.php&amp;edititem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'><strong><?php echo $item->name; ?></strong></a></td>
								<td style='background: #FFF;text-align:right'><?php echo $item->dayname; ?></td>
								<td style='background: #FFF;text-align:right'>
								<?php 
								
								if ($options['timeformat'] == '24hours')
									$hour = floor($item->starttime);
								elseif ($options['timeformat'] == '12hours')
								{
									if ($item->starttime < 12)
									{
										$timeperiod = "am";
										if ($item->starttime == 0)
											$hour = 12;
										else
											$hour = floor($item->starttime);
									}
									else
									{
										$timeperiod = "pm";
										if ($item->starttime == 12)
											$hour = $item->starttime;
										else
											$hour = floor($item->starttime) - 12;
									}
								}
								
								if (fmod($item->starttime, 1) == 0.25)
                                    $minutes = "15";
								elseif (fmod($item->starttime, 1) == 0.50)
									$minutes = "30";
								elseif (fmod($item->starttime, 1) == 0.75)
									$minutes = "45";
                                else
                                    $minutes = "00";
																	
								if ($options['timeformat'] == '24 hours')
									echo $hour . "h" . $minutes . "\n";
								else
									echo $hour . ":" . $minutes . $timeperiod . "\n";
								?></td>
								<td style='background:#FFF'><a href='?page=weekly-schedule.php&amp;deleteitem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>' 
								<?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to delete the item '%s'\n  'Cancel' to stop, 'OK' to delete."), $item->name )) . "') ) { return true;}return false;\""; ?>><img src='<?php echo $wspluginpath; ?>/icons/delete.png' /></a></td>
								</tr>
							  <?php endforeach; ?>				
							  
							  </tbody>
							  </table>
							<?php endif; ?>
				</div>
				<?php elseif ($adminpage == "days"): ?>
				<div>
					<a href="?page=weekly-schedule.php&amp;settings=general&amp;schedule=<?php echo $schedule; ?>">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items&amp;schedule=<?php echo $schedule; ?>">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days&amp;schedule=<?php echo $schedule; ?>"><strong>Manage Days Labels</strong></a><br /><br />
					<div>
						<form name="wsdaysform" action="" method="post" id="ws-config">
						<?php
						if ( function_exists('wp_nonce_field') )
							wp_nonce_field('wspp-config');
							
						$days = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsdays WHERE scheduleid = " . $schedule . " ORDER by id");
						
						if ($days):
						?>
						<input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
						<table>
						<tr>
						<th style='text-align:left'><strong>ID</strong></th><th style='text-align:left'><strong>Name</strong></th>
						</tr>
						<?php foreach($days as $day): ?>
							<tr>
								<td style='width:30px;'><?php echo $day->id; ?></td><td><input style="width:300px" type="text" name="<?php echo $day->id; ?>" value='<?php echo $day->name; ?>'/></td>
							</tr>
						<?php endforeach; ?>
						</table>					
						
						<p style="border:0;" class="submit"><input type="submit" name="updatedays" value="Update &raquo;" /></p>
						
						<?php endif; ?>
						
						</form>
					</div>
				</div>
				<?php endif; ?>				
			</div>
			<?php
		} // end config_page()

	} // end class WS_Admin
} //endif

function get_wsdays(){	}

function ws_library_func($atts) {
	extract(shortcode_atts(array(
		'schedule' => ''
	), $atts));
	
	if ($schedule == '')
	{
		$options = get_option('WS_PP1');
		$schedule = 1;
	}
	else
	{
		$schedulename = 'WS_PP' . $schedule;
		$options = get_option($schedulename);
	}
	
	if ($options == false)
	{
		return "Requested schedule (Schedule " . $schedule . ") is not available from Weekly Schedule<br />";
	}
	
	return ws_library($schedule, $options['starttime'], $options['endtime'], $options['timedivision'], $options['layout'], $options['tooltipwidth'], $options['tooltiptarget'],
					  $options['tooltippoint'], $options['tooltipcolorscheme'], $options['displaydescription'], $options['daylist'], $options['timeformat'],
					  $options['adjusttooltipposition']);
}

	
function ws_library($scheduleid = 1, $starttime = 19, $endtime = 22, $timedivision = 0.5, $layout = 'horizontal', $tooltipwidth = 300, $tooltiptarget = 'rightMiddle',
					$tooltippoint = 'leftMiddle', $tooltipcolorscheme = 'cream', $displaydescription = 'tooltip', $daylist = '', $timeformat = '24hours',
					$adjusttooltipposition = true) {
	global $wpdb;	
	
	$numberofcols = ($endtime - $starttime) / $timedivision;
	$linktarget = "newwindow";
	
	$output = "<!-- Weekly Schedule Output -->\n";

	$output .= "<div class='ws-schedule' id='ws-schedule<?php echo $scheduleid; ?>'>\n";
	
	if ($layout == 'horizontal' || $layout == '')
	{
		$output .= "<table>\n";	
	}
	elseif ($layout == 'vertical')
	{
		$output .= "<div class='verticalcolumn'>\n";
		$output .= "<table class='verticalheader'>\n";
	}
	
	$output .= "<tr class='topheader'>";

	$output .= "<th class='rowheader'></th>";
	
	if ($layout == 'vertical')
	{
		$output .= "</tr>\n";
	}

	for ($i = $starttime; $i < $endtime; $i += $timedivision)	{
	
	if (fmod($i, 1) == 0.25)
		$minutes = "15";
	elseif (fmod($i, 1) == 0.50)
		$minutes = "30";
	elseif (fmod($i, 1) == 0.75)
		$minutes = "45";
	else
		$minutes = "";


		if ($timeformat == "24hours" || $timeformat == "")
		{
			if ($layout == 'vertical')
				$output .= "<tr class='datarow'>";
			
			$output .= "<th>" .  floor($i) . "h" . $minutes . "</th>";
			
			if ($layout == 'vertical')
				$output .= "</tr>\n";
			
		}
		else if ($timeformat == "12hours")
		{
			if ($i < 12)
			{
				$timeperiod = "am";
				if ($i == 0)
					$hour = 12;
				else
					$hour = floor($i);
			}
			else
			{
				$timeperiod = "pm";
				if ($i >= 12 && $i < 13)
					$hour = floor($i);
				else
					$hour = floor($i) - 12;
			}
			
			if ($layout == 'vertical')
				$output .= "<tr class='datarow'>";
			
			$output .= "<th>" . $hour;
			if ($minutes != "")
				$output .= ":" . $minutes;
			$output .=  $timeperiod . "</th>";			
			
			if ($layout == 'vertical')
				$output .= "</tr>\n";
		}
	}

	if ($layout == 'horizontal' || $layout == '')
		$output .= "</tr>\n";
	elseif ($layout == 'vertical')
	{
		$output .= "</table>\n";
		$output .= "</div>\n";
	}


 	$sqldays = "SELECT * from " .  $wpdb->prefix . "wsdays where scheduleid = " . $scheduleid;
	
	if ($daylist != "")
		$sqldays .= " AND id in (" . $daylist . ") ORDER BY FIELD(id, " . $daylist. ")";
		
	$daysoftheweek = $wpdb->get_results($sqldays);

	foreach ($daysoftheweek as $day)
	{
		for ($daysrow = 1; $daysrow <= $day->rows; $daysrow++)
		{
			$columns = $numberofcols;
			$time = $starttime;
			
			if ($layout == 'vertical')
			{
				$output .= "<div class='verticalcolumn'>\n";
				$output .= "<table class='vertical'>\n";
				$output .= "<tr class='vertrow'>";
			}
			elseif ($layout == 'horizontal' || $layout == '')
			{
				$output .= "<tr class='row" . $day->rows . "'>\n";
			}

			if ($daysrow == 1 && ($layout == 'horizontal' || $layout == ''))
				$output .= "<th rowspan='" . $day->rows . "' class='rowheader'>" . $day->name . "</th>\n";
			if ($daysrow == 1 && $layout == 'vertical' && $day->rows == 1)
				$output .= "<th class='rowheader'>" . $day->name . "</th>\n";
			if ($daysrow == 1 && $layout == 'vertical' && $day->rows > 1)
				$output .= "<th class='rowheader'>&laquo; " . $day->name . "</th>\n";				
			elseif ($daysrow != 1 && $layout == 'vertical')
			{
				if ($daysrow == $day->rows)
					$output .= "<th class='rowheader'>" . $day->name . " &raquo;</th>\n";
				else
					$output .= "<th class='rowheader'>&laquo; " . $day->name . " &raquo;</th>\n";
			}
				
			if ($layout == 'vertical')
				$output .= "</tr>\n";

			$sqlitems = "SELECT *, i.name as itemname, c.name as categoryname, c.id as catid from " . $wpdb->prefix . 
						"wsitems i, " . $wpdb->prefix . "wscategories c WHERE day = " . $day->id . 			
						" AND i.scheduleid = " . $scheduleid . " AND row = " . $daysrow . " AND i.category = c.id AND i.starttime >= " . $starttime . " AND i.starttime < " .
						$endtime . " ORDER by starttime";

			$items = $wpdb->get_results($sqlitems);

			if ($items)
			{
				foreach($items as $item)
				{
					for ($i = $time; $i < $item->starttime; $i += $timedivision)
					{
						if ($layout == 'vertical')
							$output .= "<tr class='datarow'>\n";
							
						$output .= "<td></td>\n";
						
						if ($layout == 'vertical')
							$output .= "</tr>\n";
						
						$columns -= 1;

					}
					
					$colspan = $item->duration / $timedivision;
					
					if ($colspan > $columns)
					{
						$colspan = $columns;
						$columns -= $columns;
						
						if ($layout == 'horizontal')
							$continue .= "id='continueright' ";
						elseif ($layout == 'vertical')
							$continue .= "id='continuedown' ";
					}
					else
					{					
						$columns -= $colspan;
						$continue = "";
					}	
					
					if ($layout == 'vertical')
							$output .= "<tr class='datarow" . $colspan . "'>";
					
					$output .= "<td ";
					
					if ($displaydescription == "tooltip" && $item->description != "")
						$output .= "tooltip='" . htmlspecialchars(stripslashes($item->description),  ENT_QUOTES) . "' ";
					
					$output .= $continue;
					
					if ($layout == 'horizontal' || $layout == '')
						$output .= "colspan='" . $colspan . "' ";
					
					$output .= "class='cat" . $item->catid . "'>";
					
					if ($item->address != "")
						$output .= "<a target='" . $linktarget . "'href='" . $item->address. "'>";
						
					$output .= $item->itemname;
										
					if ($item->address != "")
						"</a>";
						
					if ($displaydescription == "cell")
						$output .= "<br />" .  stripslashes($item->description);
						
					$output .= "</td>";
					$time = $item->starttime + $item->duration;
					
					if ($layout == 'vertical')
						$output .= "</tr>\n";
					
				}

				for ($x = $columns; $x > 0; $x--)
				{
				
					if ($layout == 'vertical')
							$output .= "<tr class='datarow'>";
					
					$output .= "<td></td>";
					$columns -= 1;
					
					if ($layout == 'vertical')
							$output .= "</tr>";
				}
			}
			else
			{
				for ($i = $starttime; $i < $endtime; $i += $timedivision)
				{
					if ($layout == 'vertical')
							$output .= "<tr class='datarow'>";
							
					$output .= "<td></td>";
					
					if ($layout == 'vertical')
							$output .= "</tr>";
				}
			}

			if ($layout == 'horizontal' || $layout == '')
				$output .= "</tr>";
			
			if ($layout == 'vertical')
			{
				$output .= "</table>\n";
				$output .= "</div>\n";
			}
		}
	}

	$output .= "</table>";

	$output .= "</div id='ws-schedule'>\n";
	
	if ($displaydescription == "tooltip")
	{
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "// Create the tooltips only on document load\n";	
		
		$output .= "jQuery(document).ready(function()\n";
		$output .= "\t{\n";
		$output .= "\t// Notice the use of the each() method to acquire access to each elements attributes\n";
		$output .= "\tjQuery('.ws-schedule td[tooltip]').each(function()\n";
		$output .= "\t\t{\n";
		$output .= "\t\tjQuery(this).qtip({\n";
		$output .= "\t\t\tcontent: jQuery(this).attr('tooltip'), // Use the tooltip attribute of the element for the content\n";
		$output .= "\t\t\tstyle: {\n";
		$output .= "\t\t\t\twidth: " . $tooltipwidth . ",\n";
		$output .= "\t\t\t\tname: '" . $tooltipcolorscheme . "', // Give it a crea mstyle to make it stand out\n";
		$output .= "\t\t\t},\n";
		$output .= "\t\t\tposition: {\n";
		if ($adjusttooltipposition)
			$output .= "\t\t\t\tadjust: {screen: true},\n";
		$output .= "\t\t\t\tcorner: {\n";
		$output .= "\t\t\t\t\ttarget: '" . $tooltiptarget . "',\n";
		$output .= "\t\t\t\t\ttooltip: '" . $tooltippoint . "'\n";
		$output .= "\t\t\t\t}\n";
		$output .= "\t\t\t}\n";
		$output .= "\t\t});\n";
		$output .= "\t});\n";
		$output .= "});\n";
		$output .= "</script>\n";
	}
	
	$output .= "<!-- End of Weekly Schedule Output -->\n";

 	return $output;
}

$version = "1.0";

// adds the menu item to the admin interface
add_action('admin_menu', array('WS_Admin','add_config_page'));

add_shortcode('weekly-schedule', 'ws_library_func');

add_filter('the_posts', 'ws_conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head

function ws_conditionally_add_scripts_and_styles($posts){
	if (empty($posts)) return $posts;
	
	$load_jquery = false;
	$load_qtip = false;
	$load_style = false;
	
	$genoptions = get_option('WeeklyScheduleGeneral');

	foreach ($posts as $post) {		
			$continuesearch = true;
			$searchpos = 0;
			$scheduleids = array();
			
			while ($continuesearch) 
			{
				$weeklyschedulepos = stripos($post->post_content, 'weekly-schedule ', $searchpos);
				if ($weeklyschedulepos == false)
				{
					$weeklyschedulepos = stripos($post->post_content, 'weekly-schedule]', $searchpos);
				}
				$continuesearch = $weeklyschedulepos;
				if ($continuesearch)
				{
					$load_style = true;
					$shortcodeend = stripos($post->post_content, ']', $weeklyschedulepos);
					if ($shortcodeend)
						$searchpos = $shortcodeend;
					else
						$searchpos = $weeklyschedulepos + 1;
						
					if ($shortcodeend)
					{
						$settingconfigpos = stripos($post->post_content, 'settings=', $weeklyschedulepos);
						if ($settingconfigpos && $settingconfigpos < $shortcodeend)
						{
							$schedule = substr($post->post_content, $settingconfigpos + 9, $shortcodeend - $settingconfigpos - 9);
								
							$scheduleids[] = $schedule;
						}
						else if (count($scheduleids) == 0)
						{
							$scheduleids[] = 1;
						}
					}
				}	
			}
		}
		
		if ($scheduleids)
		{
			foreach ($scheduleids as $scheduleid)
			{
				$schedulename = 'WS_PP' . $scheduleid;
				$options = get_option($schedulename);			
				
				if ($options['displaydescription'] == "tooltip")
				{
					$load_jquery = true;
					$load_qtip = true;
				}					
			}
		}
			
		if ($genoptions['includescriptcss'] != '')
		{
			$pagelist = explode (',', $genoptions['includescriptcss']);
			foreach($pagelist as $pageid) {
				if (is_page($pageid))
				{
					$load_jquery = true;
					$load_style = true;
					$load_qtip = true;				
				}
			}
		}
	
	if ($load_style)
	{		
		if ($genoptions == "")
			$genoptions['stylesheet'] = 'stylesheet.css';
			
		wp_enqueue_style('weeklyschedulestyle', get_bloginfo('wpurl') . '/wp-content/plugins/weekly-schedule/' . $genoptions['stylesheet']);	
	}
 
	if ($load_jquery)
	{
		wp_enqueue_script('jquery');
	}
	
	if ($load_qtip)
	{
		wp_enqueue_script('qtip', get_bloginfo('wpurl') . '/wp-content/plugins/weekly-schedule/jquery-qtip/jquery.qtip-1.0.min.js');
	}
	 
	return $posts;
}

?>