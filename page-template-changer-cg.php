<?php
/*
Plugin Name: Page Template Changer (Caved Geeks)
Plugin URI: www.cavedgeeks.com
Description: This plugin allows for the changing of a page's template between two given specified times.
Version: 0.1.3
Author: Rashid	
Author URI: www.cavedgeeks.com
License: GPL2 
*/
	function isTimeBetween($start, $end, $input) {
	    $f = DateTime::createFromFormat('!H:i', $start);
	    $t = DateTime::createFromFormat('!H:i', $end);
	    $i = DateTime::createFromFormat('!H:i', $input);
	    if ($f > $t) $t->modify('+1 day');
	    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
	}

	function template_page_time_check( $original_template ) {
		//For debugging purposes
		/*
		ini_set('display_errors',1);
		ini_set('display_startup_errors',1);
		error_reporting(-1);
		*/

		$page_selected = get_option('page_selected');

		if ( is_page($page_selected ) ) {
			//check if the function is enabled
			$enabled = get_option('page_template_enabled');

			if ($enabled) {
				//Using digits 0 - 23 to represent the 24 hour clock, please select the hour you would like the change to start.
		        $change_hour_start = get_option('page_template_starting_hour');;
		        //Using digits 0 - 59 to represent minutes, please select the minutes with the hour you would like the change to star. 
		        $change_minute_start = get_option('page_template_starting_minute');

		        //Using digits 0 - 23 to represent the 24 hour clock, please select the hour you would like the change to end.
		        $change_hour_end = get_option('page_template_ending_hour');
		        //Using digits 0 - 59 to represent minutes, please select the minutes with the hour you would like the change to end. 
		        $change_minute_end = get_option('page_template_ending_minute');

		        //The filename of the template to use
		        $alt_template_path = get_option('page_template_alternate');

		        //enure the values are formatted correctly
		        $change_hour_start = intval($change_hour_start);
		        $change_hour_start = ( $change_hour_start < 10 ) ? '0'.$change_hour_start : $change_hour_start;

		        $change_minute_start = intval($change_minute_start);
		        $change_minute_start = ( $change_minute_start < 10 ) ? '0'.$change_minute_start : $change_minute_start;

		        $change_hour_end = intval($change_hour_end);
		        $change_hour_end = ( $change_hour_end < 10 ) ? '0'.$change_hour_end : $change_hour_end;

		        $change_minute_end = intval($change_minute_end);
		        $change_minute_end = ( $change_minute_end < 10 ) ? '0'.$change_minute_end : $change_minute_end;

		        $start = $change_hour_start.':'.$change_minute_start;
		        $end = $change_hour_end.':'.$change_minute_end;

		        //Change which timezone to use
		        //More information here http://php.net/manual/en/timezones.php
		        date_default_timezone_set( get_option('page_template_timezone') );
		        $current_time = date('H:i', time());
		        $dir = get_stylesheet_directory();

		        if ( isTimeBetween($start, $end, $current_time) ){ 
					return locate_template($alt_template_path);
				} else {
					//otherwise return the original template
					return $original_template;
				}
			}
		}

		return $original_template;
	}

	add_filter( 'template_include', 'template_page_time_check' );
?>

<?php 
	class options_page {
		function __construct() {
			add_action( 'admin_menu', array( $this, 'ptc_admin_menu' ) );
			
		}

		function ptc_admin_menu () {
			add_options_page( 
				'Page Template Changer Settings',
				'Page Template Changer',
				'manage_options',
				'page_template_changer_cg', 
				array( $this, 'settings_page' ) 
			);

			add_action( 'admin_init', array( $this, 'ptc_register_settings' ) );
		}

		function ptc_register_settings () {
			//register settings 
			register_setting( 'ptc-settings-group', 'page_template_enabled');
			register_setting( 'ptc-settings-group', 'page_selected');
			register_setting( 'ptc-settings-group', 'page_template_alternate');
			register_setting( 'ptc-settings-group', 'page_template_starting_hour');
			register_setting( 'ptc-settings-group', 'page_template_starting_minute');
			register_setting( 'ptc-settings-group', 'page_template_ending_hour');
			register_setting( 'ptc-settings-group', 'page_template_ending_minute');
			register_setting( 'ptc-settings-group', 'page_template_timezone');
		}

		function  settings_page () {
			/* load the jquery script */
			wp_enqueue_script('jquery');
		?>
			<style type="text/css">
				.focus {
					font-size: 1.2em;
					color: #39c16d;
				}


			</style>

			<div class="wrap">
				<h2>Page Template Changer (CG)</h2>

				<form method="post" action="options.php">
					<?php settings_fields( 'ptc-settings-group' ); ?>
					<table class="form-table">
						<tr valign="top">
					        <th scope="row">Enabled?</th>
					        <td>
				        		<input type="checkbox" name="page_template_enabled" value="enabled" />
				        	</td>
				        </tr>


				        <tr valign="top">
				        	<th scope="row">Select a page</th>
				        	<td>
				        		<select autocomplete="off" name="page_selected" id="page_selected">
									<option value="<?php echo get_option('page_on_front'); ?>">FRONT PAGE</option>
									<?php
										$pages = get_pages();
									    foreach ( $pages as $page ) {
									       	echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
									    }
									?>
								</select>
				        	</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row">Select an alternate template</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_alternate" id="page_template_alternate">
									<?php
										$templates = get_page_templates();
									    foreach ( $templates as $template_name => $template_filename ) {
									       	echo '<option value="'.$template_filename.'">'.$template_name.' ('.$template_filename.')</option>';
									    }
									?>
								</select>
				        	</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><span class="focus">Starting Hour</span> at which to change the template [0-23]</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_starting_hour" id="page_template_starting_hour" >
									<?php
										for($x = 0; $x < 24; $x++) {
											$val = $x;

											if($val < 10)
												$val = '0'.$val;

											echo '<option value="'.$val.'">'.$val.'</option>';
										}
									?>
								</select> 
				        	</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><span class="focus">Starting Minute</span> at which to change the template [0-59]</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_starting_minute" id="page_template_starting_minute" >
									<?php
										for($x = 0; $x < 60; $x++) {
											$val = $x;

											if($val < 10)
												$val = '0'.$val;
											echo '<option value="'.$val.'">'.$val.'</option>';
										}
									?>
								</select>
				        	</td>
				        </tr>

				        <br />

				        <tr valign="top">
				        	<th scope="row"><span class="focus">Ending Hour</span> at which to change the template [0-23]</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_ending_hour" id="page_template_ending_hour" >
									<?php
										for($x = 0; $x < 24; $x++) {
											$val = $x;

											if($val < 10)
												$val = '0'.$val;

											echo '<option value="'.$val.'">'.$val.'</option>';
										}
									?>
								</select> 
				        	</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><span class="focus">Ending Minute</span> at which to change the template [0-59]</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_ending_minute" id="page_template_ending_minute" >
									<?php
										for($x = 0; $x < 60; $x++) {
											$val = $x;

											if($val < 10)
												$val = '0'.$val;
											echo '<option value="'.$val.'">'.$val.'</option>';
										}
									?>
								</select>
				        	</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row">Copy and paste the timezone you wish to use.</th>
				        	<td>
				        		<select autocomplete="off" name="page_template_timezone" id="page_template_timezone" >
									<?php
										$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

										echo '<option value="America/New_York">Eastern - America/New_York</option>';
										echo '<option value="America/Chicago">Central - America/Chicago</option>';
										echo '<option value="America/Los_Angeles">Pacific - America/Los_Angeles</option>';

										$tzlist = array_diff($tzlist, array('America/New_York', 'America/Chicago', 'America/Los_Angeles'));
										foreach( $tzlist as $tz){
												echo '<option value="'.$tz.'">'.$tz.'</option>';
										}

									?>
								</select>

								<div id="timezone_time_container">
									<h4>Current time is: <span id="timezone_time" class="focus"></span></h4>
								</div>
				        	</td>
				        </tr>

				        <tr>
				    </table>

				     <p class="submit">
    					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    				</p>
				</form>
			</div>
			
			<script type="text/javascript">
				jQuery(document).ready(function(){
					$ = jQuery;
					var page_template_enabled = "<?php echo get_option('page_template_enabled'); ?>";
					var page_selected = "<?php echo get_option('page_selected'); ?>";
					var page_template_alternate = "<?php echo get_option('page_template_alternate'); ?>";
					var page_template_starting_hour = "<?php echo get_option('page_template_starting_hour'); ?>";
					var page_template_starting_minute = "<?php echo get_option('page_template_starting_minute'); ?>";
					var page_template_ending_hour = "<?php echo get_option('page_template_ending_hour'); ?>";
					var page_template_ending_minute = "<?php echo get_option('page_template_ending_minute'); ?>";
					var page_template_timezone = "<?php echo get_option('page_template_timezone'); ?>";

					//set the default values
					if(page_template_enabled.length > 0) {
						$('input[name="page_template_enabled"]').attr('checked', true); // Deprecated
						$('input[name="page_template_enabled"]').prop('checked', true);
					}

					if(page_selected.length == 0) {
						$('#page_selected').val( $('#page_selected option:first').val() );
					} else {
						$('#page_selected').val(page_selected);
					}

					if(page_template_alternate.length == 0) {
						$('#page_template_alternate').val( $('#page_template_alternate option:first').val() );
					} else {
						$('#page_template_alternate').val(page_template_alternate);
					}

					if(page_template_starting_hour.length == 0) {
						$('#page_template_starting_hour').val( $('#page_template_starting_hour option:first').val() );
					} else {
						$('#page_template_starting_hour').val(page_template_starting_hour);
					}

					if(page_template_starting_minute.length == 0) {
						$('#page_template_starting_minute').val( $('#page_template_starting_minute option:first').val() );
					} else {
						$('#page_template_starting_minute').val(page_template_starting_minute);
					}

					if(page_template_ending_hour.length == 0) {
						$('#page_template_ending_hour').val( $('#page_template_ending_hour option:first').val() );
					} else {
						$('#page_template_ending_hour').val(page_template_ending_hour);
					}

					if(page_template_ending_minute.length == 0) {
						$('#page_template_ending_minute').val( $('#page_template_ending_minute option:first').val() );
					} else {
						$('#page_template_ending_minute').val(page_template_ending_minute);
					}

					if(page_template_timezone.length == 0) {
						$('#page_template_timezone').val( 'America/Barbados' );
					} else {
						$('#page_template_timezone').val(page_template_timezone);
					}

					/* auto load the time for the timezone selected */
					changeTime();

					/* load the time every time the timezone is changed */
					$('#page_template_timezone').change(function(){
						changeTime();
					});
				});

				function changeTime(){
					var timezone = $('#page_template_timezone').val();
					$('#timezone_time_container').fadeOut("fast");

					$.get("<?php echo plugins_url(); ?>/page-template-changer-cg/getTime.php?time="+timezone, function(time){
						$('#timezone_time').html(time);
							$('#timezone_time_container').fadeIn();
					});
				}
			</script>
		<?php
		}
	}
	new options_page;
?>