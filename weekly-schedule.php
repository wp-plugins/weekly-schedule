<?php
/*Plugin Name: Weekly Schedule
Plugin URI: http://yannickcorner.nayanna.biz/wordpress-plugins/
Description: A plugin used to create a page with a list of TV shows
Version: 1.1.2
Author: Yannick Lefebvre
Author URI: http://yannickcorner.nayanna.biz   
Copyright 2009  Yannick Lefebvre  (email : ylefebvre@gmail.com)    

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
				PRIMARY KEY  (`id`)
				) $charset_collate"); 
				
	$catsresult = $wpdb->query("
			SELECT * from `$wpdb->wscategories`");
			
	if (!$catsresult)
		$result = $wpdb->query("
			INSERT INTO `$wpdb->wscategories` (`id`, `name`) VALUES
			(1, 'Default')");				
				
	$wpdb->wsdays = $wpdb->prefix.'wsdays';
	
	$result = $wpdb->query("
			CREATE TABLE IF NOT EXISTS `$wpdb->wsdays` (
				`id` int(10) unsigned NOT NULL,
				`name` varchar(12) NOT NULL,
				`rows` int(10) unsigned NOT NULL,
				PRIMARY KEY  (`id`)
				)  $charset_collate"); 
				
	$daysresult = $wpdb->query("
			SELECT * from `$wpdb->wsdays`");
			
	if (!$daysresult)
		$result = $wpdb->query("
			INSERT INTO `$wpdb->wsdays` (`id`, `name`, `rows`) VALUES
			(1, 'Sun', 1),
			(2, 'Mon', 1),
			(3, 'Tue', 1),
			(4, 'Wes', 1),
			(5, 'Thu', 1),
			(6, 'Fri', 1),
			(7, 'Sat', 1)");
			
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
				PRIMARY KEY  (`id`)
			) $charset_collate");

	$options  = get_option('WS_PP',"");

	if ($options == "") {
		$options['starttime'] = 19;
		$options['endtime'] = 22;
		$options['timedivision'] = 0.5;
		$options['tooltipwidth'] = 300;
		$options['tooltiptarget'] = 'rightMiddle';
		$options['tooltippoint'] = 'leftMiddle';
		$options['tooltipcolorscheme'] = 'cream';
		$options['stylesheet'] = "stylesheet.css";
		$options['displaydescription'] = "tooltip";
		$options['daylist'] = "";
		$options['timeformat'] = "24hours";
		$options['layout'] = 'horizontal';
		
		update_option('WS_PP',$options);
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
				add_filter( 'ozh_adminmenu_icon', array( 'WS_Admin', 'add_ozh_adminmenu_icon' ) );
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

			if ( isset($_GET['reset']) && $_GET['reset'] == "true") {
				
				update_option('WS_PP',$options);
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
					$items = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE MOD(duration, 1) <> 0");
					
					if ($items)
					{
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have half-hourly durations</strong></div>';
						$options['timedivision'] = "0.5";
					}
					else
						$options['timedivision'] = $_POST['timedivision'];					
				}
				else
					$options['timedivision'] = $_POST['timedivision'];

				foreach (array('starttime','endtime','tooltipwidth','tooltiptarget','tooltippoint','tooltipcolorscheme',
						'stylesheet','displaydescription','daylist', 'timeformat', 'layout') as $option_name) {
						if (isset($_POST[$option_name])) {
							$options[$option_name] = $_POST[$option_name];
						}
					}
					
				update_option('WS_PP', $options);
				
				echo '<div id="message" class="updated fade"><p><strong>Weekly Schedule Settings Updated</strong></div>';
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
					$newcat = array("name" => $_POST['name']);
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
								
				$selecteditem = $wpdb->get_row("select * from " . $wpdb->prefix . "wsitems where id = " . $_GET['edititem']);
			}
			if (isset($_POST['newitem']) || isset($_POST['updateitem']))
			{
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
									 "day" => $_POST['day']);
									 
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
						$conflictquery .= " and ((" . $newitem['starttime'] . " < starttime and " . $endtime . " > starttime) or";
						$conflictquery .= "      (" . $newitem['starttime'] . " >= starttime and " . $newitem['starttime'] . " < starttime + duration))";
						
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
								$itemday = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays WHERE id = " . $origday);
								
								$othersonrow = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE day = " . $origday . " AND row = " . $origrow . "AND id != " . $_POST['id']);
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
									
									$dayid = array("id" => $itemday->id);
									$newrow = $itemday->rows - 1;
									$newdayrow = array("rows" => $newrow);
									
									$wpdb->update($wpdb->prefix . 'wsdays', $newdayrow, $dayid);
								}
							}							
						}
					}
					
					$dayrow = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays where id = " . $_POST['day']);
					if ($dayrow->rows < $row)
					{
						$dayid = array("id" => $_POST['day']);
						$newdayrow = array("rows" => $row);
						
						$wpdb->update($wpdb->prefix . 'wsdays', $newdayrow, $dayid);
					}
					
					$newitem['row'] = $row;
						
					if (isset($_POST['id']))
						$id = array("id" => $_POST['id']);
						
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
				
				$itemexist = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsitems WHERE id = " . $_GET['deleteitem']);
				$itemday = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "wsdays WHERE id = " . $itemexist->day);
				
				if ($itemexist)
				{
					$wpdb->query("DELETE from " . $wpdb->prefix . "wsitems WHERE id = " . $_GET['deleteitem']);
					
					if ($itemday->rows > 1)
					{						
						$othersonrow = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsitems WHERE day = " . $itemexist->day . " AND row = " . $itemexist->row);
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
							
							$dayid = array("id" => $itemexist->day);
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
					$dayidarray = array("id" => $dayid);
					
					$wpdb->update($wpdb->prefix . 'wsdays', $daynamearray, $dayidarray);
				}					
			}
			
			$wspluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

			$options  = get_option('WS_PP');
			?>
			<div class="wrap">
				<h2>Weekly Schedule Configuration</h2>		
				<?php if (($adminpage == "") || ($adminpage == "general")): ?>
				<a href="?page=weekly-schedule.php&amp;settings=general"><strong>General Settings</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=categories">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days">Manage Days Labels</a><br /><br />
				<form name="wsadminform" action="" method="post" id="ws-config">
				<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					<strong>Time-related Settings</strong><br />
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
					<?php for ($i = 0; $i < 24.5; $i+= 0.5)
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
										if ($i == 12)
											$hour = $i;
										else
											$hour = floor($i) - 12;
									}
								}
								
								if (fmod($i, 1))
									$minutes = "30";
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
					<?php for ($i = 0; $i < 24.5; $i+= 0.5)
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
										if ($i == 12)
											$hour = $i;
										else
											$hour = floor($i) - 12;
									}
								}
								
								if (fmod($i, 1))
									$minutes = "30";
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
					<td><select style='width: 200px' name='timedivision'>
					<?php $timedivisions = array("0.5" => "Half-Hourly", "1.0" => "Hourly");
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
					<tr>
					<td colspan="4">Stylesheet File (should be in weekly-schedule plugin folder)</td></tr>
					<tr><td colspan="4"><input type='text' name='stylesheet' style='width: 200px' value='<?php echo $options['stylesheet']; ?>' /></td>
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
					</table>
					<p style="border:0;" class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
				</form>
				<?php /* --------------------------------------- Categories --------------------------------- */ ?>
				<?php elseif ($adminpage == "categories"): ?>
				<a href="?page=weekly-schedule.php&amp;settings=general">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories"><strong>Manage Schedule Categories</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=items">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days">Manage Days Labels</a><br /><br />
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
					<?php if ($mode == "edit"): ?>
						<p style="border:0;" class="submit"><input type="submit" name="updatecat" value="Update &raquo;" /></p>
					<?php else: ?>
						<p style="border:0;" class="submit"><input type="submit" name="newcat" value="Insert New Category &raquo;" /></p>
					<?php endif; ?>
					</form>
				</div>
				<div>
					<?php $cats = $wpdb->get_results("SELECT count( i.id ) AS nbitems, c.name, c.id FROM " . $wpdb->prefix . "wscategories c LEFT JOIN " . $wpdb->prefix . "wsitems i ON i.category = c.id GROUP BY c.name");
					
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
								<td style='background: #FFF'><a href='?page=weekly-schedule.php&amp;editcat=<?php echo $cat->id; ?>'><strong><?php echo $cat->name; ?></strong></a></td>
								<td style='background: #FFF;text-align:right'><?php echo $cat->nbitems; ?></td>
								<?php if ($cat->nbitems == 0): ?>
								<td style='background:#FFF'><a href='?page=weekly-schedule.php&amp;deletecat=<?php echo $cat->id; ?>' 
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
				<a href="?page=weekly-schedule.php&amp;settings=general">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items"><strong>Manage Schedule Items</strong></a> | <a href="?page=weekly-schedule.php&amp;settings=days">Manage Days Labels</a><br /><br />
				<div style='float:left;margin-right: 15px;width: 500px;'>
					<form name="wsitemsform" action="" method="post" id="ws-config">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					<input type="hidden" name="id" value="<?php if ($mode == "edit") echo $selecteditem->id; ?>" />
					<input type="hidden" name="oldrow" value="<?php if ($mode == "edit") echo $selecteditem->row; ?>" />
					<input type="hidden" name="oldday" value="<?php if ($mode == "edit") echo $selecteditem->day; ?>" />
					<?php if ($mode == "edit"): ?>
					<strong>Editing Item #<?php echo $selecteditem->id; ?></strong>
					<?php endif; ?>

					<table>
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('wspp-config');
					?>
					<tr>
					<td style='width: 100px'>Item Title</td>
					<td><input style="width:400px" type="text" name="name" <?php if ($mode == "edit") echo "value='" . $selecteditem->name . "'";?>/></td>
					</tr>
					<tr>
					<td>Category</td>
					<td><select style='width: 400px' name="category">
					<?php $cats = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "wscategories ORDER by name");
					
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
					<td><textarea id="description" rows="5" cols="50" name="description"><?php if ($mode == "edit") echo  stripslashes($selecteditem->description);?></textarea></td>
					</tr>
					<tr>
					<td>Web Address</td>
					<td><input style="width:400px" type="text" name="address" <?php if ($mode == "edit") echo "value='" . $selecteditem->address . "'";?>/></td>
					</tr>
					<tr>
					<td>Day</td><td><select style='width: 400px' name="day">
					<?php $days = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "wsdays ORDER by id");
					
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
					<td><select style='width: 400px' name="starttime">
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
										if ($i == 12)
											$hour = $i;
										else
											$hour = floor($i) - 12;
									}
								}
									
								if (fmod($i, 1))
									$minutes = "30";
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
					<td><select style='width: 400px' name="duration">
					<?php for ($i = $options['timedivision']; $i <= ($options['endtime'] - $options['starttime']); $i += $options['timedivision'])
						  {
								if (fmod($i, 1))
									$minutes = "30";
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
				<?php $items = $wpdb->get_results("SELECT d.name as dayname, i.id, i.name, i.day, i.starttime FROM " . $wpdb->prefix . "wsitems as i, " . $wpdb->prefix . "wsdays as d WHERE i.day = d.id ORDER by day, starttime, name");
					
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
								<td style='background: #FFF'><a href='?page=weekly-schedule.php&amp;edititem=<?php echo $item->id; ?>'><strong><?php echo $item->name; ?></strong></a></td>
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
									
								if (fmod($item->starttime, 1))
									$minutes = "30";
								else
									$minutes = "00";
																	
								if ($options['timeformat'] == '24 hours')
									echo $hour . "h" . $minutes . "\n";
								else
									echo $hour . ":" . $minutes . $timeperiod . "\n";
								?></td>
								<td style='background:#FFF'><a href='?page=weekly-schedule.php&amp;deleteitem=<?php echo $item->id; ?>' 
								<?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to delete the item '%s'\n  'Cancel' to stop, 'OK' to delete."), $item->name )) . "') ) { return true;}return false;\""; ?>><img src='<?php echo $wspluginpath; ?>/icons/delete.png' /></a></td>
								</tr>
							  <?php endforeach; ?>				
							  
							  </tbody>
							  </table>
							<?php endif; ?>
				</div>
				<?php elseif ($adminpage == "days"): ?>
				<div>
					<a href="?page=weekly-schedule.php&amp;settings=general">General Settings</a> | <a href="?page=weekly-schedule.php&amp;settings=categories">Manage Schedule Categories</a> | <a href="?page=weekly-schedule.php&amp;settings=items">Manage Schedule Items</a> | <a href="?page=weekly-schedule.php&amp;settings=days"><strong>Manage Days Labels</strong></a><br /><br />
					<div>
						<form name="wsdaysform" action="" method="post" id="ws-config">
						<?php
						if ( function_exists('wp_nonce_field') )
							wp_nonce_field('wspp-config');
							
						$days = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "wsdays ORDER by id");
						
						if ($days):
						?>
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

		function restore_defaults() {
			update_option('WS_PP',$options);
		}
	} // end class WS_Admin
} //endif

function get_wsdays(){	}

function ws_library_func($atts) {
	extract(shortcode_atts(array(	), $atts));
	return ws_library();
	}
	
function ws_library() {
	global $wpdb;
	
	$options = get_option('WS_PP');
	
	$numberofcols = ($options['endtime'] - $options['starttime']) / $options['timedivision'];
	$linktarget = "newwindow";
	
	$output = "<!-- Weekly Schedule Output -->\n";

	$output .= "<div id='ws-schedule'>\n";
	
	if ($options['layout'] == 'horizontal' || $options['layout'] == '')
	{
		$output .= "<table>\n";	
	}
	elseif ($options['layout'] == 'vertical')
	{
		$output .= "<div class='verticalcolumn'>\n";
		$output .= "<table class='verticalheader'>\n";
	}
	
	$output .= "<tr class='topheader'>";

	$output .= "<th class='rowheader'></th>";
	
	if ($options['layout'] == 'vertical')
	{
		$output .= "</tr>\n";
	}

	for ($i = $options['starttime']; $i < $options['endtime']; $i += $options['timedivision'])	{

		if (fmod($i, 1))
			$minutes = "30";
		else
			$minutes = "";

		if ($options['timeformat'] == "24hours" || $options['timeformat'] == "")
		{
			if ($options['layout'] == 'vertical')
				$output .= "<tr class='datarow'>";
			
			$output .= "<th>" .  floor($i) . "h" . $minutes . "</th>";
			
			if ($options['layout'] == 'vertical')
				$output .= "</tr>\n";
			
		}
		else if ($options['timeformat'] == "12hours")
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
				if ($i == 12)
					$hour = $i;
				else
					$hour = floor($i) - 12;
			}
			
			if ($options['layout'] == 'vertical')
				$output .= "<tr class='datarow'>";
			
			$output .= "<th>" . $hour;
			if ($minutes != "")
				$output .= ":" . $minutes;
			$output .=  $timeperiod . "</th>";			
			
			if ($options['layout'] == 'vertical')
				$output .= "</tr>\n";
		}
	}

	if ($options['layout'] == 'horizontal' || $options['layout'] == '')
		$output .= "</tr>\n";
	elseif ($options['layout'] == 'vertical')
	{
		$output .= "</table>\n";
		$output .= "</div>\n";
	}


 	$sqldays = "SELECT * from " .  $wpdb->prefix . "wsdays";
	
	if ($options['daylist'] != "")
		$sqldays .= " WHERE id in (" . $options['daylist'] . ") ORDER BY FIELD(id, " . $options['daylist']. ")";
		
	$daysoftheweek = $wpdb->get_results($sqldays);

	foreach ($daysoftheweek as $day)
	{
		for ($daysrow = 1; $daysrow <= $day->rows; $daysrow++)
		{
			$columns = $numberofcols;
			$time = $options['starttime'];
			
			if ($options['layout'] == 'vertical')
			{
				$output .= "<div class='verticalcolumn'>\n";
				$output .= "<table class='vertical'>\n";
				$output .= "<tr class='vertrow'>";
			}
			elseif ($options['layout'] == 'horizontal' || $options['layout'] == '')
			{
				$output .= "<tr class='row" . $day->rows . "'>\n";
			}

			if ($daysrow == 1 && ($options['layout'] == 'horizontal' || $options['layout'] == ''))
				$output .= "<th rowspan='" . $day->rows . "' class='rowheader'>" . $day->name . "</th>\n";
			if ($daysrow == 1 && $options['layout'] == 'vertical' && $day->rows == 1)
				$output .= "<th class='rowheader'>" . $day->name . "</th>\n";
			if ($daysrow == 1 && $options['layout'] == 'vertical' && $day->rows > 1)
				$output .= "<th class='rowheader'>&laquo; " . $day->name . "</th>\n";				
			elseif ($daysrow != 1 && $options['layout'] == 'vertical')
			{
				if ($daysrow == $day->rows)
					$output .= "<th class='rowheader'>" . $day->name . " &raquo;</th>\n";
				else
					$output .= "<th class='rowheader'>&laquo; " . $day->name . " &raquo;</th>\n";
			}
				
			if ($options['layout'] == 'vertical')
				$output .= "</tr>\n";

			$sqlitems = "SELECT *, i.name as itemname, c.name as categoryname, c.id as catid from " . $wpdb->prefix . 
						"wsitems i, " . $wpdb->prefix . "wscategories c WHERE day = " . $day->id . 			
						" AND row = " . $daysrow . " AND i.category = c.id AND i.starttime >= " . $options['starttime'] . " AND i.starttime < " .
						$options['endtime'] . " ORDER by starttime";

			$items = $wpdb->get_results($sqlitems);

			if ($items)
			{
				foreach($items as $item)
				{
					for ($i = $time; $i < $item->starttime; $i += $options['timedivision'])
					{
						if ($options['layout'] == 'vertical')
							$output .= "<tr class='datarow'>\n";
							
						$output .= "<td></td>\n";
						
						if ($options['layout'] == 'vertical')
							$output .= "</tr>\n";
						
						$columns -= 1;

					}
					
					$colspan = $item->duration / $options['timedivision'];
					
					if ($colspan > $columns)
					{
						$colspan = $columns;
						$columns -= $columns;
						
						if ($options['layout'] == 'horizontal')
							$continue .= "id='continueright' ";
						elseif ($options['layout'] == 'vertical')
							$continue .= "id='continuedown' ";
					}
					else
					{					
						$columns -= $colspan;
						$continue = "";
					}	
					
					if ($options['layout'] == 'vertical')
							$output .= "<tr class='datarow" . $colspan . "'>";
					
					$output .= "<td ";
					
					if ($options['displaydescription'] == "tooltip" && $item->description != "")
						$output .= "tooltip='" . stripslashes($item->description) . "' ";
					
					$output .= $continue;
					
					if ($options['layout'] == 'horizontal' || $options['layout'] == '')
						$output .= "colspan='" . $colspan . "' ";
					
					$output .= "class='cat" . $item->catid . "'>";
					
					if ($item->address != "")
						$output .= "<a target='" . $linktarget . "'href='" . $item->address. "'>";
						
					$output .= $item->itemname;
										
					if ($item->address != "")
						"</a>";
						
					if ($options['displaydescription'] == "cell")
						$output .= "<br />" .  stripslashes($item->description);
						
					$output .= "</td>";
					$time = $item->starttime + $item->duration;
					
					if ($options['layout'] == 'vertical')
						$output .= "</tr>\n";
					
				}

				for ($x = $columns; $x > 0; $x--)
				{
				
					if ($options['layout'] == 'vertical')
							$output .= "<tr class='datarow'>";
					
					$output .= "<td></td>";
					$columns -= 1;
					
					if ($options['layout'] == 'vertical')
							$output .= "</tr>";
				}
			}
			else
			{
				for ($i = $options['starttime']; $i < $options['endtime']; $i += $options['timedivision'])
				{
					if ($options['layout'] == 'vertical')
							$output .= "<tr class='datarow'>";
							
					$output .= "<td></td>";
					
					if ($options['layout'] == 'vertical')
							$output .= "</tr>";
				}
			}

			if ($options['layout'] == 'horizontal' || $options['layout'] == '')
				$output .= "</tr>";
			
			if ($options['layout'] == 'vertical')
			{
				$output .= "</table>\n";
				$output .= "</div>\n";
			}
		}
	}

	$output .= "</table>";

	$output .= "</div id='ws-schedule'>\n";
	
	if ($options['displaydescription'] == "tooltip")
	{
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "// Create the tooltips only on document load\n";	
		
		$output .= "jQuery(document).ready(function()\n";
		$output .= "\t{\n";
		$output .= "\t// Notice the use of the each() method to acquire access to each elements attributes\n";
		$output .= "\tjQuery('#ws-schedule td[tooltip]').each(function()\n";
		$output .= "\t\t{\n";
		$output .= "\t\tjQuery(this).qtip({\n";
		$output .= "\t\t\tcontent: jQuery(this).attr('tooltip'), // Use the tooltip attribute of the element for the content\n";
		$output .= "\t\t\tstyle: {\n";
		$output .= "\t\t\t\twidth: " . $options['tooltipwidth'] . ",\n";
		$output .= "\t\t\t\tname: '" . $options['tooltipcolorscheme'] . "', // Give it a crea mstyle to make it stand out\n";
		$output .= "\t\t\t},\n";
		$output .= "\t\t\tposition: {\n";
		$output .= "\t\t\t\tcorner: {\n";
		$output .= "\t\t\t\t\ttarget: '" . $options['tooltiptarget'] . "',\n";
		$output .= "\t\t\t\t\ttooltip: '" . $options['tooltippoint'] . "'\n";
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

function ws_library_header()
{
	$options = get_option('WS_PP');
	echo '<link rel="stylesheet" type="text/css" media="screen" href="' . WP_PLUGIN_URL . '/weekly-schedule/' . $options['stylesheet'] . '"/>';
}

function weekly_schedule_init() {
	wp_enqueue_script('qtip', get_bloginfo('wpurl') . '/wp-content/plugins/weekly-schedule/jquery-qtip/jquery.qtip-1.0.0-rc3.min.js');
}  


// adds the menu item to the admin interface
add_action('admin_menu', array('WS_Admin','add_config_page'));

add_action('init', 'weekly_schedule_init');

wp_enqueue_script('jquery');

add_action('wp_head', 'ws_library_header');

add_shortcode('weekly-schedule', 'ws_library_func');

?>