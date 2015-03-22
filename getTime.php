<?php
	if( isset($_GET['time']) ) {
		$timezone = $_GET['time'];

		if( !empty($timezone) ) {
			date_default_timezone_set( $timezone );

			$current_time = date('H:i', time());

			echo $current_time;
		}
	}
?>