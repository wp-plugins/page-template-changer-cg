<?php
/*
Plugin Name: Page Template Changer (Caved Geeks)
Plugin URI: www.cavedgeeks.com
Description: This plugin allows for the changing of a page's template between two given specified times.
Version: 0.1
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

		$page_check = get_theme_mod('page_selected');

		if ( is_front_page() ) {
			//check if the function is enabled
			$enabled = get_theme_mod('page_template_enabled');

			if ($enabled) {
				//Using digits 0 - 23 to represent the 24 hour clock, please select the hour you would like the change to start.
		        $change_hour_start = get_theme_mod('page_template_starting_hour');;
		        //Using digits 0 - 59 to represent minutes, please select the minutes with the hour you would like the change to star. 
		        $change_minute_start = get_theme_mod('page_template_starting_minute');

		        //Using digits 0 - 23 to represent the 24 hour clock, please select the hour you would like the change to end.
		        $change_hour_end = get_theme_mod('page_template_ending_hour');
		        //Using digits 0 - 59 to represent minutes, please select the minutes with the hour you would like the change to end. 
		        $change_minute_end = get_theme_mod('page_template_ending_minute');

		        //The filename of the template to use
		        $alt_template_path = get_theme_mod('page_template_alternate');

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
		        date_default_timezone_set( get_theme_mod('page_template_timezone') );
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

	function customize_page_template_time_check( $wp_customize ) {
		if ( ! class_exists( 'WP_Customize_Control' ) )
		return NULL;

		//Custom class to pick from the pages avaliable 
		class Pages_Dropdown_Custom_Control extends WP_Customize_Control {
			public function render_content() {
				?>
					<label>
						<span class="customize-category-select-control customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<select <?php $this->link(); ?> >
							<option value="<?php echo get_option('page_on_front'); ?>">FRONT PAGE</option>
						<?php
							$pages = get_pages();;
						    foreach ( $pages as $page ) {
						       	echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
						    }
						?>
						</select>
					</label>
				<?php
			}
		}
		
		//Custom class to pick from the template files provided
		class Templates_Dropdown_Custom_Control extends WP_Customize_Control {
			public function render_content() {
				?>
					<label>
						<span class="customize-category-select-control customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<select <?php $this->link(); ?> >
						<?php
							$templates = get_page_templates();
						    foreach ( $templates as $template_name => $template_filename ) {
						       	echo '<option value="'.$template_filename.'">'.$template_name.' ('.$template_filename.')</option>';
						    }
						?>
						</select>
					</label>
				<?php
			}
		}

		//Custom class to pick hours
		class Hours_Dropdown_Custom_Control extends WP_Customize_Control {
			public function render_content() {
				?>
					<label>
						<span class="customize-category-select-control customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<select <?php $this->link(); ?> >
						<?php
							for($x = 0; $x < 24; $x++) {
								$val = $x;

								if($val < 10)
									$val = '0'.$val;

								echo '<option value="'.$val.'">'.$val.'</option>';
							}
						?>
						</select> 
					</label>
				<?php
			}
		}

		//Custom class to pick minutes
		class Minutes_Dropdown_Custom_Control extends WP_Customize_Control {
			public function render_content() {
				?>
					<label>
						<span class="customize-category-select-control customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<select <?php $this->link(); ?> >
						<?php
							for($x = 0; $x < 60; $x++) {
								$val = $x;

								if($val < 10)
									$val = '0'.$val;
								echo '<option value="'.$val.'">'.$val.'</option>';
							}
						?>
						</select>
					</label>
				<?php
			}
		}

		//Custom class to pick timezones
		class Timezones_Dropdown_Custom_Control extends WP_Customize_Control {
			public function render_content() {
				?>
					<label>
						<span class="customize-category-select-control customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<select <?php $this->link(); ?> >
						<?php
							$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

							foreach( $tzlist as $tz){
								echo '<option value="'.$tz.'">'.$tz.'</option>';
							}

						?>
						</select>
					</label>
				<?php
			}
		}


		
	   	//All our sections, settings, and controls will be added here
		$wp_customize->add_section( 'page_template_time_check', array(
	    	'title'      => __( 'Page Template Changer', 'time_check' ),
	    	'priority'   => 999,
		) );

		$wp_customize->add_setting('page_template_enabled', array(
			'default' => 0,
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'page_template_enabled',
				array(
					'label' => __('Enabled? [You may have to refresh this page to see changes]', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_enabled',
					'type'			 => 'checkbox',
	            )
			)
		);

		$wp_customize->add_setting('page_selected', array(
			'default' => -1,
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Pages_Dropdown_Custom_Control(
				$wp_customize,
				'page_selected',
				array(
					'label'   => 'Select a Page',
	            	'section' => 'page_template_time_check',
	            	'settings'   => 'page_selected',
				)
			)
		);

		$wp_customize->add_setting('page_template_alternate', array(
			'default' => 'Please select a template',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Templates_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_alternate',
				array(
					'label'   => 'Select a template',
	            	'section' => 'page_template_time_check',
	            	'settings'   => 'page_template_alternate',
				)
			)
		);

		$wp_customize->add_setting('page_template_starting_hour', array(
			'default' => '12',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Hours_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_starting_hour',
				array(
					'label' => __('Starting Hour at which to change the template [0-23]', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_starting_hour',
	            )
			)
		);

		$wp_customize->add_setting('page_template_starting_minute', array(
			'default' => '30',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Minutes_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_starting_minute',
				array(
					'label' => __('Starting Minute at which to change the template [0-59]', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_starting_minute',
	            )
			)
		);

		$wp_customize->add_setting('page_template_ending_hour', array(
			'default' => '12',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Hours_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_ending_hour',
				array(
					'label' => __('Ending Hour at which to change the template [0-23]', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_ending_hour',
	            )
			)
		);

		$wp_customize->add_setting('page_template_ending_minute', array(
			'default' => '30',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Minutes_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_ending_minute',
				array(
					'label' => __('Starting Minute at which to change the template [0-59]', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_ending_minute',
	            )
			)
		);

		$wp_customize->add_setting('page_template_timezone', array(
			'default' => 'America/Barbados',
			'transport' => 'refresh',
		));

		$wp_customize->add_control(
			new Timezones_Dropdown_Custom_Control(
				$wp_customize,
				'page_template_timezone',
				array(
					'label' => __('Copy and paste the timezone you wish to use.', 'time_check'),
					'section'        => 'page_template_time_check',
					'settings'       => 'page_template_timezone',
	            )
			)
		);
	}

	add_action( 'customize_register', 'customize_page_template_time_check' );
?>