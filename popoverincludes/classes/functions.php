<?php

function set_popover_url($base) {

	global $popover_url;

	if(defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename($base))) {
		$popover_url = trailingslashit(WPMU_PLUGIN_URL);
	} elseif(defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/wordpress-popup/' . basename($base))) {
		$popover_url = trailingslashit(WP_PLUGIN_URL . '/wordpress-popup');
	} else {
		$popover_url = trailingslashit(WP_PLUGIN_URL . '/wordpress-popup');
	}

}

function set_popover_dir($base) {

	global $popover_dir;

	if(defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename($base))) {
		$popover_dir = trailingslashit(WPMU_PLUGIN_URL);
	} elseif(defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/wordpress-popup/' . basename($base))) {
		$popover_dir = trailingslashit(WP_PLUGIN_DIR . '/wordpress-popup');
	} else {
		$popover_dir = trailingslashit(WP_PLUGIN_DIR . '/wordpress-popup');
	}


}

function popover_url($extended) {

	global $popover_url;

	return $popover_url . $extended;

}

function popover_dir($extended) {

	global $popover_dir;

	return $popover_dir . $extended;


}
?>