<?php
// Load dependencies.
require_once PO_INC_DIR . 'class-popup-item.php';
require_once PO_INC_DIR . 'class-popup-database.php';
require_once PO_INC_DIR . 'class-popup-posttype.php';
require_once PO_INC_DIR . 'class-popup-rule.php';

// Load extra functions.
require_once PO_INC_DIR . 'addons/class-popup-addon-headerfooter.php';
require_once PO_INC_DIR . 'addons/class-popup-addon-anonymous.php';
require_once PO_INC_DIR . 'addons/class-popup-addon-geo-db.php';

/**
 * Defines common functions that are used in admin and frontpage.
 */
abstract class IncPopupBase {

	/**
	 * Holds the IncPopupDatabase instance.
	 * @var IncPopupDatabase
	 */
	protected $db = null;

	/**
	 * List of PopUps fetched by select_popup()
	 * @var array [IncPopupItem]
	 */
	protected $popups = array();

	/**
	 * Data collection for compatibility with other plugins.
	 * @var array
	 */
	protected $compat_data = array();


	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->db = IncPopupDatabase::instance();

		// Prepare the URL
		if ( ! empty( $_REQUEST['orig_request_uri'] ) ) {
			$this->prepare_url();
			add_action( 'init', array( $this, 'revert_url' ), 999 ); // prevent 404.
			add_action( 'parse_query', array( $this, 'prepare_url' ) );
		}

		WDev()->translate_plugin( PO_LANG, PO_LANG_DIR );

		// Register the popup post type.
		add_action(
			'init',
			array( 'IncPopupPosttype', 'instance' ),
			99
		);

		// Register the popup post type.
		add_action(
			'wp_loaded',
			array( 'IncPopupDatabase', 'check_db' )
		);

		// Load active add-ons.
		add_action(
			'init',
			array( 'IncPopup', 'load_optional_files' )
		);

		// Returns a list of all style infos (id, url, path, deprecated)
		add_filter(
			'popup-styles',
			array( 'IncPopupBase', 'style_infos' )
		);

		// Ajax handlers to load PopUp data (for logged in users).
		add_action(
			'wp_ajax_inc_popup',
			array( $this, 'ajax_load_popup' )
		);

		// Ajax handlers to load PopUp data (for guests).
		add_action(
			'wp_ajax_nopriv_inc_popup',
			array( $this, 'ajax_load_popup' )
		);

		// Compatibility with plugins
		if ( function_exists( 'get_rocket_option' ) && get_rocket_option( 'minify_js' ) ) {
			foreach ( array( 'edit-inc_popup', 'inc_popup', 'inc_popup_page_settings' ) as $screen ) {
				WDev()->message(
					__(
						'You are using WP Rocket with JS Minification, which has ' .
						'caused some issues in the past. We recommend to disable ' .
						'the JS Minification setting in WP Rocket to avoid problems.',
						PO_LANG
					),
					'err',
					$screen
				);
			}
		}

		// Tell Add-ons and extensions that we are set up.
		do_action( 'popup-init' );
	}

	/**
	 * If the plugin operates on global multisite level.
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function use_global() {
		$State = null;

		if ( null === $State ) {
			$State = is_multisite() && PO_GLOBAL;
		}

		return $State;
	}

	/**
	 * If the the user currently is on correct level (global vs blog)
	 *
	 * @since  4.6
	 * @return bool
	 */
	static public function correct_level() {
		$State = null;

		if ( null === $State ) {
			$State = false;
			if ( ! self::use_global() ) {
				if ( is_admin() && ! is_network_admin() ) {
					$State = true;
				}
			} else {
				$blog_id = defined( 'BLOG_ID_CURRENT_SITE' )  ? BLOG_ID_CURRENT_SITE : 0;
				if ( is_network_admin() || $blog_id == get_current_blog_id() ) {
					$State = true;
				}
			}
		}

		return $State;
	}

	/**
	 * Returns an array with all core popup styles.
	 *
	 * @since  4.6
	 * @return array Core Popup styles.
	 */
	static protected function _get_styles( ) {
		$Styles = null;

		if ( null === $Styles ) {
			$Styles = array();
			if ( $handle = opendir( PO_TPL_DIR ) ) {
				while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( $entry === '.' || $entry === '..' ) { continue; }
					$style_file = PO_TPL_DIR . $entry . '/style.php';
					if ( ! file_exists( $style_file ) ) { continue; }

					$info = (object) array();
					include $style_file;

					$Styles[ $entry ] = $info;
				}
				closedir( $handle );
			}
		}

		return $Styles;
	}

	/**
	 * Returns a list of all style infos (id, url, path, deprecated)
	 * Handles filter `popup-styles`
	 *
	 * @since  4.6
	 * @param  array $list
	 * @return array The updated list.
	 */
	static public function style_infos( $list = array() ) {
		$core_styles = self::_get_styles();
		$urls = apply_filters( 'popup-available-styles-url', array() );
		$paths = apply_filters( 'popup-available-styles-directory', array() );

		if ( ! is_array( $list ) ) { $list = array(); }

		// Add core styles to the response.
		foreach ( $core_styles as $key => $data ) {
			$list[ $key ] = (object) array(
				'url' => trailingslashit( PO_TPL_URL . $key ),
				'dir' => trailingslashit( PO_TPL_DIR . $key ),
				'name' => $data->name,
				'pro' => (true == @$data->pro),
				'deprecated' => (true == @$data->deprecated),
			);
			if ( isset( $urls[$data->name] ) ) { unset( $urls[$data->name] ); }
			if ( isset( $paths[$data->name] ) ) { unset( $paths[$data->name] ); }
		}

		// Add custom styles to the response.
		foreach ( $urls as $key => $url ) {
			$list[ $key ] = (object) array(
				'url' => $url,
				'dir' => @$paths[$key],
				'name' => $key,
				'pro' => false,
				'deprecated' => false,
			);
		}

		return $list;
	}

	/**
	 * Returns a list with all available rule-files.
	 *
	 * @since  4.6
	 * @return array List of rule-files.
	 */
	static public function get_rules() {
		$List = null;

		if ( null === $List ) {
			$List = array();
			$base_len = strlen( PO_INC_DIR . 'rules/' );
			foreach ( glob( PO_INC_DIR . 'rules/*.php' ) as $path ) {
				$List[] = substr( $path, $base_len );
			}

			$List = apply_filters( 'popup-available-rules', $List );

			sort( $List );
		}

		return $List;
	}

	/**
	 * Loads the active Add-On files.
	 *
	 * @since  4.6
	 */
	static public function load_optional_files() {
		$settings = IncPopupDatabase::get_settings();

		if ( $settings['geo_db'] ) {
			IncPopupAddon_GeoDB::init();
		}

		// $available uses apply_filter to customize the results.
		$available = self::get_rules();

		foreach ( $available as $rule ) {
			$path = PO_INC_DIR . 'rules/'. $rule;
			if ( in_array( $rule, $settings['rules'] ) && file_exists( $path ) ) {
				include_once $path;
			}
		}
	}

	/**
	 * Returns an array with all relevant popup fields extracted from the
	 * submitted form data.
	 *
	 * @since  4.6
	 * @param  array $form Raw form array, submitted from WP admin.
	 * @return array Data extracted from the $form array.
	 */
	static protected function prepare_formdata( $form ) {
		if ( ! is_array( $form ) ) { $form = array(); }


		$data = array(
			// Meta: Content
			'name' => @$form['po_name'],
			'content' => stripslashes( @$form['po_content'] ),
			'title' => @$form['po_heading'],
			'subtitle' => @$form['po_subheading'],
			'cta_label' => @$form['po_cta'],
			'cta_link' => @$form['po_cta_link'],
			'image' => @$form['po_image'],
			'image_pos' => @$form['po_image_pos'],
			'image_mobile' => ! isset( $form['po_image_no_mobile'] ),
			'active' => isset( $form['po_active'] ),

			// Meta: Appearance
			'style' => @$form['po_style'],
			'round_corners' => ! isset( $form['po_no_round_corners'] ),
			'scroll_body' => isset( $form['po_scroll_body'] ),
			'custom_colors' => isset( $form['po_custom_colors'] ),
			'color' => @$form['po_color'],
			'custom_size' => isset( $form['po_custom_size'] ),
			'size' => array(
				'width' => @$form['po_size_width'],
				'height' => @$form['po_size_height'],
			),

			// Meta: Behavior
			'display' => @$form['po_display'],
			'display_data' => @$form['po_display_data'],
			'can_hide' => isset( $form['po_can_hide'] ),
			'close_hides' => isset( $form['po_close_hides'] ),
			'hide_expire' => @$form['po_hide_expire'],
			'overlay_close' => ! isset( $form['po_overlay_close'] ),

			// Meta: Rules:
			'rule' => @$form['po_rule'],
			'rule_data' => apply_filters( 'popup-save-rules', array() ),
		);

		if ( ! is_array( $data['rule'] ) ) { $data['rule'] = array(); }

		return $data;
	}

	/**
	 * Returns the popup content as JSON object and then ends the request.
	 *
	 * @since  4.6
	 */
	public function ajax_load_popup() {
		$action = @$_REQUEST['do'];

		switch ( $action ) {
			case 'get-data':
				if ( IncPopupItem::POST_TYPE == @$_REQUEST['data']['post_type'] ) {
					$this->popups = array();
					$this->popups[0] = new IncPopupItem();
					$data = self::prepare_formdata( $_REQUEST['data'] );
					$this->popups[0]->populate( $data );
				} else {
					$this->select_popup();
				}

				if ( ! empty( $this->popups ) ) {
					$data = $this->get_popup_data();
					echo 'po_data(' . json_encode( $data ) . ')';
				}
				die();
		}
	}

	/**
	 * Processes the popups array and returns a serializeable object with all
	 * popup details. The object can be JSON-encoded and interpreted by the
	 * javascript library.
	 *
	 * @since  4.6
	 * @return array
	 */
	public function get_popup_data() {
		$data = array();
		$is_preview = ! empty( $_REQUEST['preview'] );

		foreach ( $this->popups as $item ) {
			$item_data = $item->get_script_data( $is_preview );
			$data[] = $item_data;
		}

		return $data;
	}


	/*==================================*\
	======================================
	==                                  ==
	==           SELECT POPUP           ==
	==                                  ==
	======================================
	\*==================================*/


	/**
	 * Returns an array with popup details.
	 *
	 * @since  4.6
	 * @return array
	 */
	protected function select_popup() {
		$data = array();
		$items = $this->find_popups();
		$this->popups = array();

		if ( empty( $items ) ) {
			return;
		}

		$this->popups = $items;
	}

	/**
	 * Returns an array of PopUp objects that should be displayed for the
	 * current page/user. The PopUps are in the order in which they are defined
	 * in the admin list.
	 *
	 * @since  4.6
	 * @return array List of all popups that fit the current page.
	 */
	protected function find_popups() {
		$popups = array();

		$popup_id = absint( @$_REQUEST['po_id'] );
		if ( $popup_id ) {
			// Check for forced popup.
			$active_ids = array( $popup_id );
		} else {
			$active_ids = IncPopupDatabase::get_active_ids();
		}

		foreach ( $active_ids as $id ) {
			$popup = IncPopupDatabase::get( $id );

			if ( $popup_id ) {
				// Forced popup ignores all conditions.
				$show = true;
			} else {
				// Apply the conditions to decide if the popup should be displayed.
				$show = apply_filters( 'popup-apply-rules', true, $popup );
			}

			// Stop here if the popup failed in some conditions.
			if ( ! $show ) { continue; }

			// Stop here if the user did choose to hide the popup.
			if ( ! @$_REQUEST['preview'] && $this->is_hidden( $id ) ) { continue; }

			$popups[] = $popup;
		}

		return $popups;
	}


	/**
	 * Tests if a popup is marked as hidden ("never see this message again").
	 * This flag is stored as a cookie on the users computer.
	 *
	 * @since  4.6
	 * @param  int $id PopUp ID
	 * @return bool
	 */
	protected function is_hidden( $id ) {
		$name = 'po_h-' . $id;
		$name_old = 'popover_never_view_' . $id;

		return isset( $_COOKIE[$name] ) || isset( $_COOKIE[$name_old] );
	}


	/*===================================*\
	=======================================
	==                                   ==
	==           COMPATIBILITY           ==
	==                                   ==
	=======================================
	\*===================================*/


	/**
	 * Change the Request-URI, so other plugins use the correct form action, etc.
	 *
	 * Example:
	 *  Contact-Form-7 is included as shortcode in a PopUp.
	 *  The PopUp is loaded via WordPress Ajax.
	 *  When the Contact form is generated, the CF7 plugin will use the AJAX-URL
	 *  for the contact form, instead of the URL of the host-page...
	 *
	 * @since  4.6.1.1
	 */
	public function prepare_url() {
		if ( empty( $_REQUEST['orig_request_uri'] ) ) { return; }

		if ( empty( $this->orig_url ) ) {
			$this->orig_url = $_SERVER['REQUEST_URI'];
		}

		// Remove internal commands from the query.
		$_SERVER['REQUEST_URI'] = strtok( $_REQUEST['orig_request_uri'], '#' );
	}

	/**
	 * Revert the Request-URI to the original value.
	 *
	 * @since  4.6.1.1
	 */
	public function revert_url() {
		if ( empty( $_REQUEST['orig_request_uri'] ) ) { return; }
		if ( empty( $this->orig_url ) ) { return; }

		$_SERVER['REQUEST_URI'] = $this->orig_url;
	}



};
