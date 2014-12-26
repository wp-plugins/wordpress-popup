<?php
/**
 * HTML Helper functions
 *
 * @since 1.1.0
 */
class TheLib_1_1_1_Html extends TheLib_1_1_1_Base  {

	/* Constants for default HTML input elements. */
	const INPUT_TYPE_HIDDEN = 'hidden';
	const INPUT_TYPE_TEXT = 'text';
	const INPUT_TYPE_PASSWORD = 'password';
	const INPUT_TYPE_TEXT_AREA = 'textarea';
	const INPUT_TYPE_SELECT = 'select';
	const INPUT_TYPE_RADIO = 'radio';
	const INPUT_TYPE_SUBMIT = 'submit';
	const INPUT_TYPE_BUTTON = 'button';
	const INPUT_TYPE_CHECKBOX = 'checkbox';
	const INPUT_TYPE_IMAGE = 'image';
	const INPUT_TYPE_FILE = 'file';

	/* Constants for advanced HTML input elements. */
	const INPUT_TYPE_WP_EDITOR = 'wp_editor';
	const INPUT_TYPE_DATEPICKER = 'datepicker';
	const INPUT_TYPE_RADIO_SLIDER = 'radio_slider';
	const INPUT_TYPE_TAG_SELECT = 'tag_select';
	const INPUT_TYPE_WP_PAGES = 'wp_pages';

	/* Constants for default HTML elements. */
	const TYPE_HTML_LINK = 'html_link';
	const TYPE_HTML_SEPARATOR = 'html_separator';
	const TYPE_HTML_TEXT = 'html_text';
	const TYPE_HTML_TABLE = 'html_table';


	/**
	 * Class constructor
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		parent::__construct();
	}


	/*=====================================*\
	=========================================
	==                                     ==
	==           WPMUI-FUNCTIONS           ==
	==                                     ==
	=========================================
	\*=====================================*/


	/**
	 * Displays a WordPress like message to the user. The message is generated
	 * via Javascript after the page is fully loaded.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $text Contents of the message.
	 * @return Reference to $this for chaining.
	 */
	public function message( $text, $type = 'ok', $id = 'msg_ok', $close = true ) {
		WDev()->add_ui( 'core' );

		$data = array(
			'message' => $text,
			'type' => $type,
			'id' => $id,
			'close' => $close,
		);
		printf(
			'<script>jQuery(function(){ wpmUi.message( %s ) });</script>',
			json_encode( $data )
		);

		return $this;
	}


	/*================================*\
	====================================
	==                                ==
	==           WP POINTER           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Displays a WordPress pointer on the current admin screen.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $pointer_id Internal ID of the pointer, make sure it is unique!
	 * @param  string $html_el HTML element to point to (e.g. '#menu-appearance')
	 * @param  string $title The title of the pointer.
	 * @param  string $body Text of the pointer.
	 * @return Reference to $this for chaining.
	 */
	public function pointer( $pointer_id, $html_el, $title, $body ) {
		if ( ! is_admin() ) {
			return;
		}

		$this->_have( 'init_pointer' ) || add_action(
			'init',
			array( $this, '_init_pointer' )
		);
		$this->_add( 'init_pointer', compact( 'pointer_id', 'html_el', 'title', 'body' ) );

		return $this;
	}

	/**
	 * Action handler for plugins_loaded. This decides if the pointer will be displayed.
	 *
	 * @since  1.0.2
	 *
	 * @private
	 */
	public function _init_pointer() {
		$items = $this->_get( 'init_pointer' );
		foreach ( $items as $item ) {
			extract( $item );

			// Find out which pointer IDs this user has already seen.
			$seen = (string) get_user_meta(
				get_current_user_id(),
				'dismissed_wp_pointers',
				true
			);
			$seen_list = explode( ',', $seen );

			// Handle our first pointer announcing the plugin's new settings screen.
			if ( ! in_array( $pointer_id, $seen_list ) ) {
				$this->_have( 'pointer' ) || add_action(
					'admin_print_footer_scripts',
					array( $this, '_pointer_print_scripts' )
				);
				$this->_have( 'pointer' ) || add_action(
					'admin_enqueue_scripts',
					array( $this, '_enqueue_pointer' )
				);
				$this->_add( 'pointer', $item );
			}
		}
	}

	/**
	 * Enqueue wp-pointer (for PHP <5.3 only)
	 *
	 * @since  1.0.1
	 *
	 * @private
	 */
	public function _enqueue_pointer() {
		// Load the JS/CSS for WP Pointers
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
	}

	/**
	 * Action hook for admin footer scripts (for PHP <5.3 only)
	 *
	 * @since  1.0.1
	 *
	 * @private
	 */
	public function _pointer_print_scripts() {
		$items = $this->_get( 'pointer' );
		foreach ( $items as $item ) {
			extract( $item ); // pointer_id, html_el, title, body
			include $this->_view_path( 'pointer.php' );
		}
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           PLUGIN LIST           ==
	==                                 ==
	=====================================
	\*=================================*/


	/**
	 * Generates full code for a plugin list in WordPress 4.0 style, including
	 * the filter and search section in the top.
	 *
	 * All items are included in page load and displayed or filtered via
	 * javascript.
	 *
	 * @since  1.1
	 *
	 * @param  array $items {
	 *     List of all items to include. Each item has these properties:
	 *
	 *     @var string $title
	 *     @var string $description
	 *     @var string $version
	 *     @var string $author
	 *     @var array $action
	 *     @var array $details
	 *     @var bool $active
	 *     @var string $icon
	 * }
	 * @param  object $lang {
	 *     @var string $active_badge
	 *     @var string $show_details
	 *     @var string $close_details
	 * }
	 * @param  array $filters {
	 *     @var string $key
	 *     @var string $label
	 * }
	 * @return Reference to $this for chaining.
	 */
	public function plugin_list( $items, $lang, $filters ) {
		WDev()->add_ui( 'card_list' );
		include $this->_view_path( 'list.php' );
		return $this;
	}


	/*====================================*\
	========================================
	==                                    ==
	==           HTML STRUCTURE           ==
	==                                    ==
	========================================
	\*====================================*/


	/**
	 * Method for creating HTML elements/fields.
	 *
	 * Pass in array with field arguments. See $defaults for argmuments.
	 * Use constants to specify field type. e.g. self::INPUT_TYPE_TEXT
	 *
	 * @since 1.1.0
	 *
	 * @return void|string If $return param is false the HTML will be echo'ed,
	 *           otherwise returned as string
	 */
	public function element( $field_args, $return = false ) {
		WDev()->add_ui( 'html_element' );

		if ( is_string( $field_args ) ) {
			if ( $return ) {
				return $field_args;
			} else {
				echo '' . $field_args;
				return;
			}
		}

		// Field arguments.
		$defaults = array(
			'id'             => '',
			'name'           => '',
			'section'        => '', // Only used if name is empty
			'title'          => '', // Title above desc / element
			'desc'           => '', // Usually displayed in row above the element
			'before'         => '', // In same row as element
			'after'          => '', // In same row as element
			'value'          => '',
			'type'           => 'text',
			'class'          => '',
			'maxlength'      => '',
			'equalTo'        => '',
			'field_options'  => array(),
			'multiple'       => false,
			'tooltip'        => '',
			'alt'            => '',
			'read_only'      => false,
			'placeholder'    => '',
			'data_placeholder' => '',
			'ajax_data'      => '',
			'data_ms'        => '', // alias for "ajax_data"
			'label_type'     => 'label',
			// Specific for type 'button', 'submit':
			'button_value'   => '',
			'button_type'    => '',  // for display [empty/'submit'/'button']
			// Specific for type 'tag_select':
			'title_selected' => '',
			'empty_text'     => '',
			'button_text'    => '',
			// Specific for type 'link':
			'target'         => '_self',
			// Specific for type 'radio_slider':
			'url'            => '',
		);

		$field_args = wp_parse_args( $field_args, $defaults );
		extract( $field_args );

		if ( empty( $name ) ) {
			if ( ! empty( $section ) ) {
				$name = $section . "[$id]";
			} else {
				$name = $id;
			}
		}

		// Input arguments

		$attr_placeholder = '';
		$attr_data_placeholder = '';

		if ( '' !== $placeholder && false !== $placeholder ) {
			$attr_placeholder = 'placeholder="' . esc_attr( $placeholder ) . '" ';
		}

		if ( '' !== $data_placeholder && false !== $data_placeholder ) {
			$attr_data_placeholder = 'data-placeholder="' . esc_attr( $data_placeholder ) . '" ';
		}

		if ( ! empty( $data_ms ) && empty( $ajax_data ) ) {
			$ajax_data = $data_ms;
		}

		if ( ! empty( $ajax_data ) ) {
			if ( empty( $ajax_data['_wpnonce'] ) && ! empty( $ajax_data['action'] ) ) {
				$ajax_data['_wpnonce'] = wp_create_nonce( $ajax_data['action'] );
			}

			$ajax_data = ' data-ajax="' . esc_attr( json_encode( $ajax_data ) ) . '" ';
		}

		$max_attr = empty( $maxlength ) ? '' : 'maxlength="' . esc_attr( $maxlength ) . '" ';
		$read_only = empty( $read_only ) ? '' : 'readonly="readonly" ';
		$multiple = empty( $multiple ) ? '' : 'multiple="multiple" ';

		if ( ! empty( $ajax_data ) ) {
			$class .= ' wpmui-ajax-update';
		}

		$labels = (object) array(
			'title' => $title,
			'desc' => $desc,
			'before' => $before,
			'after' => $after,
			'tooltip' => $tooltip,
			'tooltip_code' => $this->tooltip( $tooltip, true ),
			'id' => $id,
			'class' => '',
			'label_type' => $label_type,
		);


		// Capture to output buffer
		if ( $return ) { ob_start(); }

		switch ( $type ) {
			case self::INPUT_TYPE_HIDDEN:
				$this->element_hidden(
					$id,
					$name,
					$value
				);
				break;

			case self::INPUT_TYPE_TEXT:
			case self::INPUT_TYPE_PASSWORD:
			case self::INPUT_TYPE_FILE:
				$this->element_input(
					$labels,
					$type,
					$class,
					$id,
					$name,
					$value,
					$read_only . $max_attr . $attr_placeholder . $ajax_data
				);
				break;

			case self::INPUT_TYPE_DATEPICKER:
				$this->element_datepicker(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$max_attr . $attr_placeholder . $ajax_data
				);
				break;

			case self::INPUT_TYPE_TEXT_AREA:
				$this->element_textarea(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$read_only . $attr_placeholder . $ajax_data
				);
				break;

			case self::INPUT_TYPE_SELECT:
				$this->element_select(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$multiple . $read_only . $attr_data_placeholder . $ajax_data,
					$field_options
				);
				break;

			case self::INPUT_TYPE_RADIO:
				$this->element_radio(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$ajax_data,
					$field_options
				);
				break;

			case self::INPUT_TYPE_CHECKBOX:
				$this->element_checkbox(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$ajax_data,
					$field_options
				);
				break;

			case self::INPUT_TYPE_WP_EDITOR:
				$this->element_wp_editor(
					$labels,
					$id,
					$value,
					$field_options
				);
				break;

			case self::INPUT_TYPE_BUTTON:
			case self::INPUT_TYPE_SUBMIT:
				if ( empty( $button_type ) ) {
					$button_type = $type;
				}

				if ( $button_type === self::INPUT_TYPE_SUBMIT ) {
					$class .= ' wpmui-submit button-primary';
				}


				$this->element_button(
					$labels,
					$type,
					$class,
					$id,
					$name,
					$value,
					$button_value,
					$ajax_data
				);
				break;

			case self::INPUT_TYPE_IMAGE:
				$this->element_image(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$alt,
					$ajax_data
				);
				break;

			case self::INPUT_TYPE_RADIO_SLIDER:
				$this->element_radioslider(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$url,
					$read_only,
					$ajax_data,
					$field_options
				);
				break;

			case self::INPUT_TYPE_TAG_SELECT:
				$this->element_tagselect(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$field_options,
					$multiple . $read_only . $attr_data_placeholder,
					$ajax_data,
					$empty_text,
					$button_text,
					$title_selected
				);
				break;

			case self::INPUT_TYPE_WP_PAGES:
				$this->element_wp_pages(
					$labels,
					$class,
					$id,
					$name,
					$value,
					$multiple . $read_only . $attr_data_placeholder . $ajax_data,
					$field_options
				);
				break;

			case self::TYPE_HTML_LINK:
				$this->element_link(
					$labels,
					$class,
					$id,
					$value,
					$url,
					$ajax_data,
					$target
				);
				break;

			case self::TYPE_HTML_SEPARATOR:
				$this->element_separator(
					($value !== 'vertical' ? 'horizontal' : 'vertical')
				);
				break;

			case self::TYPE_HTML_TEXT:
				$this->element_wrapper(
					$labels,
					$class,
					$id,
					$value,
					$wrapper
				);
				break;

			case self::TYPE_HTML_TABLE:
				$this->element_table(
					$labels,
					$class,
					$id,
					$value,
					$field_options
				);
				break;
		}

		// Return the output buffer
		if ( $return ) { return ob_get_clean(); }
	}


	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_hidden( $id, $name, $value ) {
		printf(
			'<input class="wpmui-field-input wpmui-hidden" type="hidden" id="%1$s" name="%2$s" value="%3$s" />',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_input( $labels, $type, $class, $id, $name, $value, $attr ) {
		echo '<span class="wpmui-input-wrapper">';
		$this->element_label( $labels );

		printf(
			'<input class="wpmui-field-input wpmui-%1$s %2$s wpmui-input-%4$s" type="%1$s" id="%3$s" name="%4$s" value="%5$s" %6$s />',
			esc_attr( $type ),
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			$attr
		);

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_datepicker( $labels, $class, $id, $name, $value, $attr ) {
		$this->element_label( $labels );

		printf(
			'<span class="wpmui-datepicker-wrapper wpmui-field-input"><input class="wpmui-datepicker %1$s" type="text" id="%2$s" name="%3$s" value="%4$s" %5$s /><i class="wpmui-icon wpmui-fa wpmui-fa-calendar"></i></span>',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			$attr
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_textarea( $labels, $class, $id, $name, $value, $attr ) {
		$this->element_label( $labels );

		printf(
			'<textarea class="wpmui-field-input wpmui-textarea %1$s" type="text" id="%2$s" name="%3$s" %5$s>%4$s</textarea>',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( $value ),
			$attr
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_select( $labels, $class, $id, $name, $value, $attr, $field_options ) {
		$options = $this->select_options( $field_options, $value );

		echo '<span class="wpmui-select-wrapper">';
		$this->element_label( $labels );

		printf(
			'<select id="%1$s" class="wpmui-field-input wpmui-field-select %2$s" name="%3$s" %4$s>%5$s</select>',
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $name ),
			$attr,
			$options
		);

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_radio( $labels, $class, $id, $name, $value, $attr, $field_options ) {
		printf(
			'<span class="wpmui-radio-wrapper wrapper-%1$s">',
			esc_attr( $id )
		);

		$this->element_label( $labels );

		foreach ( $field_options as $key => $option ) {
			if ( is_array( $option ) ) {
				$item_text = $option['text'];
				$item_desc = $option['desc'];
			}
			else {
				$item_text = $option;
				$item_desc = '';
			}

			$checked = checked( $value, $key, false );
			$radio_desc = '';

			if ( ! empty( $item_desc ) ) {
				$radio_desc = sprintf( '<div class="wpmui-input-description"><p>%1$s</p></div>', $item_desc );
			}

			printf(
				'<div class="wpmui-radio-input-wrapper %1$s wpmui-%2$s"><label class="wpmui-field-label" for="%4$s_%2$s"><input class="wpmui-field-input wpmui-radio %1$s" type="radio" name="%3$s" id="%4$s_%2$s" value="%2$s" %5$s /><span class="wpmui-radio-caption">%6$s</span>%7$s</label></div>',
				esc_attr( $class ),
				esc_attr( $key ),
				esc_attr( $name ),
				esc_attr( $id ),
				$attr . $checked,
				$item_text,
				$radio_desc
			);
		}

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_checkbox( $labels, $class, $id, $name, $value, $attr, $options ) {
		$checked = checked( $value, true, false );

		$item_desc = '';
		if ( ! empty( $labels->desc ) ) {
			$item_desc = sprintf( '<div class="wpmui-field-description"><p>%1$s</p></div>', $labels->desc );
		}

		$item_label = '';
		if ( empty( $options['checkbox_position'] )
			|| 'left' === $options['checkbox_position']
		) {
			$item_label = sprintf(
				'<div class="wpmui-checkbox-caption">%1$s %2$s</div>',
				$labels->title,
				$labels->tooltip
			);
		}

		printf(
			'<label class="wpmui-checkbox-wrapper wpmui-field-label %2$s"><input id="%1$s" class="wpmui-field-input wpmui-field-checkbox" type="checkbox" name="%3$s" value="1" %4$s />%5$s %6$s</label>',
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $name ),
			$attr . $checked,
			$item_label,
			$item_desc
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_wp_editor( $labels, $id, $value, $options ) {
		$this->element_label( $labels );

		wp_editor( $value, $id, $options );

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_button( $labels, $type, $class, $id, $name, $label, $value, $attr ) {
		$this->element_label( $labels );

		printf(
			'<button class="wpmui-field-input button %1$s" type="%7$s" id="%2$s" name="%3$s" value="%6$s" %5$s>%4$s</button>',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $name ),
			$label,
			$attr,
			$value,
			$type
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_image( $labels, $class, $id, $name, $value, $alt, $attr ) {
		$this->element_label( $labels );

		printf(
			'<input type="image" class="wpmui-field-input wpmui-input-image %1$s" id="%2$s" name="%3$s" border="0" src="%4$s" alt="%5$s" %6$s/>',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_url( $value ),
			esc_attr( $alt ),
			$attr
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_radioslider( $labels, $class, $id, $name, $state, $url, $read_only, $attr, $options ) {
		$options = Wdev()->get_array( $options );
		if ( ! isset( $options['active'] ) ) { $options['active'] = true; }
		if ( ! isset( $options['inactive'] ) ) { $options['inactive'] = false; }

		if ( $state ) { $value = $options['active']; }
		else { $value = $options['inactive']; }

		$turned = ( $value ) ? 'on' : '';

		printf(
			'<span class="wpmui-radio-slider-wrapper %s">',
			$turned
		);

		$this->element_label( $labels );

		$attr .= ' data-states="' . esc_attr( json_encode( $options ) ) . '" ';
		$link_url = ! empty( $url ) ? '<a href="' . esc_url( $url ) . '"></a>' : '';

		$attr_input = '';
		if ( ! $read_only ) {
			$attr_input = sprintf(
				'<input class="wpmui-field-input wpmui-hidden" type="hidden" id="%1$s" name="%2$s" value="%3$s" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
		}

		printf(
			'<div class="wpmui-radio-slider %1$s wpmui-slider-%5$s %7$s" %6$s><div class="wpmui-toggle" %2$s>%3$s</div>%4$s</div>',
			esc_attr( $turned ),
			$attr,
			$link_url,
			$attr_input,
			esc_attr( $id ),
			$read_only,
			esc_attr( $class )
		);

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_tagselect( $labels, $class, $id, $name, $value, $field_options, $attr, $ajax_data, $empty_text, $button_text, $title_selected ) {
		$labels->id = '_src_' . $id;

		echo '<span class="wpmui-tag-selector-wrapper">';
		$this->element_label( $labels );

		$options_selected = '';
		$options_available = '<option value=""></option>';
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		if ( empty( $field_options ) ) {
			// No values available, display a note instead of the input elements.
			printf(
				'<div id="%1$s" class="wpmui-no-data wpmui-field-input %2$s">%3$s</div>',
				esc_attr( $id ),
				esc_attr( $class ),
				$empty_text
			);
		} else {
			// There are values to select or remove. Display the input elements.
			$options_selected .= $this->select_options( $field_options, $value );
			$options_available .= $this->select_options( $field_options, $value, 'taglist' );

			$src_class = str_replace( 'wpmui-ajax-update', '', $class );

			// First Select: The value selected here can be added to the tag-list.
			printf(
				'<select id="_src_%1$s" class="wpmui-field-input wpmui-tag-source %2$s" %4$s>%5$s</select>',
				esc_attr( $id ),
				esc_attr( $src_class ),
				esc_attr( $name ),
				$attr,
				$options_available
			);

			// Button: Add element from First Select to Second Select.
			printf(
				'<button id="_src_add_%1$s" class="wpmui-field-input wpmui-tag-button button %2$s" type="button">%3$s</button>',
				esc_attr( $id ),
				esc_attr( $src_class ),
				$button_text
			);

			$label_tag = $labels;
			$label_tag->id = $id;
			$label_tag->title = $title_selected;
			$label_tag->id = $id;
			$label_tag->tooltip = '';
			$label_tag->tooltip_code = '';
			$label_tag->class = 'wpmui-tag-label';
			$this->element_label( $label_tag );

			// Second Select: The actual tag-list
			printf(
				'<select id="%1$s" class="wpmui-field-input wpmui-field-select wpmui-tag-data %2$s" multiple="multiple" readonly="readonly" %4$s>%5$s</select>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $name ),
				$ajax_data,
				$options_selected
			);
		}

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_wp_pages( $labels, $class, $id, $name, $value, $attr, $field_options ) {
		$defaults = array(
			'hierarchical' => 1,
			'sort_column' => 'post_title',
			'sort_order' => 'ASC',
			'no_item' => '(Select a page)',
		);
		$args = wp_parse_args( $field_options, $defaults );

		$pages = get_pages( $args );
		$parent_list = array();
		$items = array();

		foreach ( $pages as $page ) {
			$parent_list[$page->ID] = $page;
		}

		if ( ! array_key_exists( $value, $parent_list ) ) {
			// In case no value is selected set the default to 'no item';
			$items[$value] = $args['no_item'];
		}

		foreach ( $pages as $page_id => $page ) {
			$level = 0;
			$parent = $page;
			while ( $parent->post_parent ) {
				$parent = $parent_list[$parent->post_parent];
				$level += 1;
			}

			if ( 0 === strlen( $page->post_title ) ) {
				$label = sprintf(
					'#%1$s (%2$s)',
					$page->ID,
					$page->post_name
				);
			} else {
				$label = $page->post_title;
			}

			$items[$page->ID] = str_repeat( '&nbsp;&mdash;&nbsp;', $level ) . $label;
		}

		$this->element_select(
			$labels,
			$class . ' wpmui-wp-pages',
			$id,
			$name,
			$value,
			$attr,
			$items
		);
	}


	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_separator( $type = 'horizontal' ) {
		if ( 'v' === $type[0] ) {
			echo '<div class="wpmui-divider"></div>';
		} else {
			echo '<div class="wpmui-separator"></div>';
		}
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_link( $labels, $class, $id, $label, $url, $attr, $target ) {
		$this->element_desc( $labels );

		if ( empty( $labels->title ) ) {
			$title = $label;
		} else {
			$title = $labels->title;
		}

		printf(
			'<a id="%1$s" title="%2$s" class="wpmui-link %3$s" href="%4$s" target="%7$s" %6$s>%5$s</a>',
			esc_attr( $id ),
			esc_attr( $title ),
			esc_attr( $class ),
			esc_url( $url ),
			$label,
			$attr,
			$target
		);

		$this->element_hint( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_wrapper( $labels, $class, $id, $code, $wrap ) {
		if ( empty( $wrap ) ) { $wrap = 'span'; }

		echo '<span class="wpmui-html-text-wrapper">';
		$this->element_label( $labels );

		printf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			esc_attr( $wrap ),
			esc_attr( $class ),
			$code
		);

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_table( $labels, $class, $id, $rows, $args ) {
		WDev()->load_fields( $args, 'head_row', 'head_col', 'col_class' );

		echo '<span class="wpmui-table-wrapper">';
		$this->element_label( $labels );

		$code_body = '';
		$code_head = '';

		if ( is_array( $rows ) ) {
			$args['col_class'] = WDev()->get_array( $args['col_class'] );

			foreach ( $rows as $row_num => $row ) {
				$code_row = '';
				$is_head_row = false;
				$row_class = $row_num % 2 === 0 ? '' : 'alternate';

				if ( 0 === $row_num && $args['head_row'] ) {
					$is_head_row = true;
				}

				if ( is_array( $row ) ) {
					foreach ( $row as $col_num => $col ) {
						$is_head = $is_head_row
							|| ( 0 === $col_num && $args['head_col'] );

						$col_class = isset( $args['col_class'][$col_num] )
							? $args['col_class'][$col_num]
							: '';

						$code_row .= sprintf(
							'<%1$s class="%3$s">%2$s</%1$s>',
							($is_head ? 'th' : 'td'),
							$col,
							$col_class
						);
					}
				} else {
					$code_row = $row;
				}

				$code_row = sprintf(
					'<tr class="%2$s">%1$s</tr>',
					$code_row,
					$row_class
				);

				if ( $is_head_row ) {
					$code_head .= $code_row;
				} else {
					$code_body .= $code_row;
				}
			}

			printf(
				'<table class="wpmui-html-table %1$s">%2$s%3$s</table>',
				esc_attr( $class ),
				'<thead>' . $code_head . '</thead>',
				'<tbody>' . $code_body . '</tbody>'
			);
		}

		$this->element_hint( $labels );
		echo '</span>';
	}

	/**
	 * Returns HTML code containing options used to build a select tag.
	 *
	 * @since  1.1.0
	 * @param  array $list List items as 'key => value' pairs.
	 * @param  array|string $value The selected value.
	 * @param  string $type Either 'default' or 'taglist'.
	 *
	 * @return string
	 */
	private function select_options( $list, $value = '', $type = 'default' ) {
		$options = '';

		foreach ( $list as $key => $option ) {
			if ( is_array( $option ) ) {
				if ( empty( $option ) ) { continue; }
				$options .= sprintf(
					'<optgroup label="%1$s">%2$s</optgroup>',
					$key,
					$this->select_options( $option, $value, $type )
				);
			} else {
				if ( is_array( $value ) ) {
					$is_selected = ( array_key_exists( $key, $value ) );
				}
				else {
					$is_selected = $key == $value;
				}

				switch ( $type ) {
					case 'default':
						$attr = selected( $is_selected, true, false );
						$options .= sprintf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $key ),
							$attr,
							$option
						);
						break;

					case 'taglist':
						$attr = ($is_selected ? 'disabled="disabled"' : '');
						$options .= sprintf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $key ),
							$attr,
							$option
						);
						break;
				}
			}
		}

		return $options;
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_label( $labels ) {
		if ( ! empty( $labels->title ) ) {
			printf(
				'<%5$s for="%1$s" class="wpmui-field-label %4$s">%2$s %3$s</%5$s>',
				esc_attr( $labels->id ),
				$labels->title,
				$labels->tooltip_code,
				esc_attr( ' wpmui-label-' . $labels->id . ' ' . $labels->class ),
				esc_attr( $labels->label_type )
			);
		}

		$this->element_desc( $labels );
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_desc( $labels ) {
		if ( ! empty( $labels->desc ) ) {
			printf(
				'<span class="wpmui-field-description %2$s">%1$s</span>',
				$labels->desc,
				esc_attr( 'wpmui-description-' . $labels->id )
			);
		}

		if ( ! empty( $labels->before ) ) {
			printf(
				'<span class="wpmui-label-before">%s</span>',
				$labels->before
			);
		}
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.1.0
	 */
	private function element_hint( $labels ) {
		if ( ! empty( $labels->after ) ) {
			printf(
				'<span class="wpmui-label-after">%s</span>',
				$labels->after
			);
		}

		if ( empty( $labels->title ) ) {
			echo '' . $labels->tooltip_code;
		}
	}

	/**
	 * Method for outputting tooltips.
	 *
	 * @since 1.1.0
	 *
	 * @return string But does output HTML.
	 */
	public function tooltip( $tip = '', $return = false ) {
		if ( empty( $tip ) ) {
			return;
		}

		if ( $return ) { ob_start(); }
		?>
		<div class="wpmui-tooltip-wrapper">
		<div class="wpmui-tooltip-info"><i class="wpmui-fa wpmui-fa-info-circle"></i></div>
		<div class="wpmui-tooltip">
			<div class="wpmui-tooltip-button">&times;</div>
			<div class="wpmui-tooltip-content">
			<?php printf( $tip ); ?>
			</div>
		</div>
		</div>
		<?php
		if ( $return ) { return ob_get_clean(); }
	}

};