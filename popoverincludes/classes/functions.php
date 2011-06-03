<?php
///////////////////////////////////////////////////////////////////////////
/* -------------------- Update Notifications Notice -------------------- */
if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );

	function wdp_un_check() {
 		if ( class_exists( 'WPMUDEV_Update_Notifications' ) )
 			return;

		if ( $delay = get_site_option( 'un_delay' ) ) {
			if ( $delay <= time() && current_user_can( 'install_plugins' ) )
				echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
		} else {
			update_site_option( 'un_delay', strtotime( "+1 week" ) );
		}
	}
}
/* --------------------------------------------------------------------- */

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