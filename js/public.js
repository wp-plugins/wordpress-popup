/*! PopUp Free - v4.6.13
 * https://wordpress.org/plugins/wordpress-popup/
 * Copyright (c) 2014; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _popup_data:false */
/*jslint evil: true */   // Allows us to keep the `fn = new Function()` line

;(function () {
	var Popup = function( _options ) {

		var me = this,
			$doc = jQuery( document ),
			$win = jQuery( window ),
			$po_div = null,
			$po_msg = null,
			$po_close = null,
			$po_hide = null,
			$po_move = null,
			$po_resize = null,
			$po_img = null,
			$po_back = null
			;

		this.data = {};
		this.have_popup = false;
		this.ajax_data = {};
		this.opened = 0;

		/**
		 * Close PopUp and set the "never see again" flag.
		 */
		this.close_forever = function close_forever() {
			var expiry = me.data.expiry || 365;

			me.close_popup();
			if ( _options['preview'] ) { return false; }

			me.set_cookie( 'po_h', 1, expiry );
			return false;
		};

		/**
		 * Close PopUp.
		 * Depending on the "multi_open" flag it can be opened again.
		 */
		this.close_popup = function close_popup() {
			jQuery( 'html' ).removeClass( 'has-popup' );

			if ( me.data.display_data['click_multi'] ) {
				$po_back.hide();
				$po_div.hide();
			} else {
				$po_back.remove();
				$po_div.remove();

				me.have_popup = false;
			}

			$doc.trigger( 'popup-closed' );
			// Legacy trigger.
			$doc.trigger( 'popover-closed' );

			popup_close( me );
			return false;
		};

		/**
		 * When user clicked on the background-layer.
		 */
		this.background_clicked = function background_clicked( ev ) {
			var el = jQuery( ev.target );

			if ( el.hasClass( 'wdpu-background' ) ) {
				if ( ! me.data.overlay_close ) { return; }

				if ( me.data.close_hide ) {
					me.close_forever();
				} else {
					me.close_popup();
				}
			}
		};

		/**
		 * Resize and move the PopUp. Triggered when PopUp is loaded and
		 * window is resized.
		 */
		this.move_popup = function move_popup() {
			var new_width, new_height, reduce_el, reduce_w_by = 0, reduce_h_by = 0;

			// Resize, if custom-size is active.
			if ( me.data.custom_size ) {
				if ( me.data.height && ! isNaN( me.data.height ) ) {
					if ( $po_resize.data( 'reduce-height' ) ) {
						reduce_el = jQuery( $po_resize.data( 'reduce-height' ) );
						reduce_h_by = reduce_el.outerHeight();
					}
					new_height = me.data.height - reduce_h_by;
					if ( new_height < 100 ) { new_height = 100; }
					$po_resize.height( new_height );
				}

				if ( me.data.width && ! isNaN( me.data.width ) ) {
					if ( $po_resize.data( 'reduce-width' ) ) {
						reduce_el = jQuery( $po_resize.data( 'reduce-width' ) );
						reduce_w_by = reduce_el.outerWidth();
					}
					new_width = me.data.width - reduce_w_by;
					if ( new_width < 100 ) { new_width = 100; }
					$po_resize.width( new_width );
				}
			}

			// This function centers the PopUp and the featured image.
			var update_position = function update_position() {
				if ( ! $po_move.hasClass( 'no-move-x' ) ) {
					var win_width = $win.width(),
						msg_width = $po_msg.outerWidth(),
						msg_left = (win_width - msg_width) / 2;

					// Move window horizontally.
					if ( msg_left < 10 ) { msg_left = 10; }
					$po_move.css({ 'left': msg_left });
				}

				if ( ! $po_move.hasClass( 'no-move-y' ) ) {
					var win_height = $win.height(),
						msg_height = $po_msg.outerHeight(),
						msg_top = (win_height - msg_height) / 2;

					// Move window vertically.
					if ( msg_top < 10 ) { msg_top = 10; }
					$po_move.css({ 'top': msg_top });
				}

				// Move the image.
				if ( $po_img.length ) {
					var offset_x, offset_y,
						img_width = $po_img.width(),
						img_height = $po_img.height(),
						box_width = $po_img.parent().width(),
						box_height = $po_img.parent().height();

					// Center horizontally.
					if ( img_width > box_width ) {
						// Center image.
						offset_x = (box_width - img_width) / 2;
						$po_img.css({ 'margin-left': offset_x });
					} else {
						// Align image according to layout.
						$po_img.css({ 'margin-left': 0 });
					}

					// Center vertially.
					if ( img_height > box_height ) {
						// Center image.
						offset_y = (box_height - img_height) / 2;
						$po_img.css({ 'margin-top': offset_y });
					} else {
						// Align image according to layout.
						$po_img.css({ 'margin-top': 0 });
					}
				}
			};

			// Short delay before positioning the popup to give the browser time
			// to show/resize the popup (20ms ~ 1 screen refresh)
			window.setTimeout(update_position, 20);
			update_position();
		};

		/**
		 * Reject the current PopUp: Do not display it.
		 */
		this.reject = function reject() {
			me.have_popup = false;
			me.data = {};
		};

		/**
		 * Check if the PopUp is ready to be displayed.
		 * If it is ready then it is displayed.
		 */
		this.prepare = function prepare() {
			me.fetch_dom();

			// Move the PopUp out of the viewport but make it visible.
			// This way the browser will start to render the contents and there
			// will be no delay when the PopUp is made visible later.
			$po_div.css({
				'opacity': 0,
				'z-index': -1,
				'position': 'fixed',
				'left': -1000,
				'width': 100,
				'right': 'auto',
				'top': -1000,
				'height': 100,
				'bottom': 'auto'
			}).show();

			$doc.trigger( 'popup-init', [me, me.data] );

			if ( me.have_popup ) {
				switch ( me.data.display ) {
					case 'scroll':
						$win.on( 'scroll', me.show_at_position );
						break;

					case 'anchor':
						$win.on( 'scroll', me.show_at_element );
						break;

					case 'delay':
						var delay = me.data.display_data.delay * 1000;
						if ( 'm' === me.data.display_data.delay_type ) {
							delay *= 60;
						}

						window.setTimeout( function() {
							popup_open( me );
						}, delay );
						break;

					default:
						// A custom action will show the PopUp (e.g. click/leave)
						window.setTimeout(function() {
							if ( 'function' === typeof me.custom_handler ) {
								me.custom_handler( me );
							}
						}, 20);
				}

			} else {
				// PopUp was rejected during popup-init event. Do not display.
			}
		};

		/**
		 * Observe the scroll-top to trigger the PopUp.
		 */
		this.show_at_position = function show_at_position( ev ) {
			var height, perc,
				el = jQuery( this ),
				top = el.scrollTop();

			if ( 'px' === me.data.display_data.scroll_type ) {
				if ( top >= me.data.display_data.scroll ) {
					$win.off( 'scroll', me.show_at_position );
					popup_open( me );
				}
			} else { // this handles '%'
				height = $doc.height() - $win.height();
				perc = 100 * top / height;

				if ( perc >= me.data.display_data.scroll ) {
					$win.off( 'scroll', me.show_at_position );
					popup_open( me );
				}
			}
		};

		/**
		 * Tests if a specific HTML element is visible to trigger the PopUp.
		 * We intentionally calculate el_top every time this function is called
		 * because the element may be hidden or not present at page load.
		 */
		this.show_at_element = function show_at_element( ev ) {
			var anchor = jQuery( me.data.display_data.anchor ),
				view_top = $win.scrollTop(),
				view_bottom = view_top + $win.height(),
				el_top = anchor.offset().top,
				offset = view_bottom - el_top;

			// When 10px of the element are visible show the PopUp.
			if ( offset > 10 ) {
				$win.off( 'scroll', me.show_at_element );
				popup_open( me );
			}
		};

		/**
		 * Can be used from custom_handler() to make a Popup visible.
		 */
		this.show_popup = function show_popup() {
			popup_open( me );
		};

		/**
		 * Display the PopUp!
		 * This function is called by popup_open() below!!!
		 */
		this._show = function _show() {
			var count;

			// If for some reason the popup container is missing then exit.
			if ( ! $po_div.length ) {
				return false;
			}

			count = parseInt( me.get_cookie('po_c'), 10 );
			if ( isNaN( count ) ) { count = 0; }
			me.set_cookie( 'po_c', count + 1 );

			me.opened += 1;
			$po_back.on( 'click', me.background_clicked );

			$win.off("resize.popup")
				.on("resize.popup", function () { me.move_popup(me.data); });

			$po_div.removeAttr( 'style' ).show();
			$po_back.show();

			if ( me.data.scroll_body ) {
				jQuery( 'html' ).addClass( 'has-popup can-scroll' );
			} else {
				jQuery( 'html' ).addClass( 'has-popup no-scroll' );
			}

			// Fix issue where Buttons are not available in Chrome
			// https://app.asana.com/0/11388810124414/18688920614102
			$po_msg.hide();
			window.setTimeout(function() {
				// The timer is so short that the element will *not* be hidden
				// but webkit will still redraw the element.
				$po_msg.show();
			}, 2);

			me.move_popup(me.data);
			me.setup_popup();

			return true;
		};

		/**
		 * Add event handlers to the PopUp controls.
		 */
		this.setup_popup = function setup_popup() {
			$po_hide.off( 'click', me.close_forever )
				.on( 'click', me.close_forever );

			if ( me.data && me.data.close_hide ) {
				$po_close.off( 'click', me.close_forever )
					.on( 'click', me.close_forever );
			} else {
				$po_close.off( 'click', me.close_popup )
					.on( 'click', me.close_popup );
			}

			$po_msg.hover(function() {
				jQuery( '.claimbutton' ).removeClass( 'hide' );
			}, function() {
				jQuery( '.claimbutton' ).addClass( 'hide' );
			});

			$doc.trigger( 'popup-displayed', [me.data, me] );
			// Legacy trigger.
			$doc.trigger( 'popover-displayed', [me.data, me] );

			$po_div.on( 'submit', 'form', me.form_submit );
		};


		/*-----  Dynamically load PopUps  ------*/


		/**
		 * Finds the PopUp DOM elements and stores them in protected member
		 * variables for easy access.
		 */
		this.fetch_dom = function fetch_dom() {
			// The top container of the PopUp.
			$po_div = jQuery( '#' + me.data['html_id'] );

			// Reject this PopUp if the HTML element is missing.
			if ( ! $po_div.length ) { me.reject(); }

			// The container that should be resized (custom size).
			$po_resize = $po_div.find( '.resize' );

			// The container that should be moved (centered on screen).
			$po_move = $po_div.find( '.move' );

			// The container that holds the message:
			// For new styles this is same as $po_resize.
			// For old popup styles this is a different contianer...
			$po_msg = $po_div.find( '.wdpu-msg' );

			// Close button.
			$po_close = $po_div.find( '.wdpu-close' );

			// Hide forever button.
			$po_hide = $po_div.find( '.wdpu-hide-forever' );

			// Featured image.
			$po_img = $po_div.find( '.wdpu-image > img' );

			// The modal background.
			if ( $po_div.hasClass( 'wdpu-background' ) ) {
				$po_back = $po_div;
			} else {
				$po_back = $po_div.find( '.wdpu-background' );

				if ( ! $po_back.length ) {
					$po_back = $po_div.parents( '.wdpu-background' );
				}
			}

			if ( ! $po_move.length ) { $po_move = $po_div; }
			if ( ! $po_resize.length ) { $po_resize = $po_div; }
		};

		/**
		 * Load popup data via ajax.
		 */
		this.load_popup = function load_popup( id, data ) {
			if ( undefined === id && _options.preview ) { return; }
			me.have_popup = false; // This object cannot display a PopUp...
			load_popups( _options, id, data );
		};

		/**
		 * A form inside the PopUp is submitted.
		 */
		this.form_submit = function form_submit( ev ) {
			var frame, form = jQuery( this ),
				popup = form.parents( '.wdpu-container' ).first(),
				msg = popup.find( '.wdpu-msg' ),
				inp_popup = jQuery( '<input type="hidden" name="_po_method_" />' ),
				po_id = '.wdpu-' + me.data.popup_id;

			if ( ! popup.length ) { return true; }

			frame = jQuery( '<iframe id="wdpu-frame" name="wdpu-frame"></iframe>' )
				.hide()
				.appendTo( 'body' );

			// Set form target to the hidden frame.
			form.attr( 'target', 'wdpu-frame' );
			inp_popup.appendTo( form ).val( 'raw' );

			msg.addClass( 'wdpu-loading' );

			jQuery( frame ).load( function(){
				var inner_new, inner_old, html;

				try {
					// grab the HTML from the body, using the raw DOM node (frame[0])
					// and more specifically, it's `contentDocument` property.
					html = jQuery( po_id, frame[0].contentDocument );
				} catch ( err ) {
					// In case the iframe link was an external website the above
					// line will most likely cause a security issue.
					html = jQuery( '<html></html>' );
				}

				msg.removeClass( 'wdpu-loading' );

				// Get the new and old Popup Contents.
				inner_new = html.find( '.wdpu-msg-inner' );
				inner_old = popup.find( '.wdpu-msg-inner' );

				// remove the temporary iframe.
				jQuery( "#wdpu-frame" ).remove();

				// In case the new popup content could not be found or is empty:
				// Close the popup!
				if ( ! inner_old.length || ! inner_new.length || ! inner_new.text().length ) {
					$doc.trigger( 'popup-submit-done', [me, me.data] );
					me.close_popup();
					return;
				}

				// Update the Popup contents.
				inner_old.replaceWith( inner_new );

				// Re-initialize the local DOM cache.
				me.fetch_dom();
				$doc.trigger( 'popup-init', [me, me.data] );

				me.move_popup();
				me.setup_popup();

				$doc.trigger( 'popup-submit-done', [me, me.data] );
			});

			return true;
		};


		/*-----  Init  ------*/


		this.init = function init() {
			if ( ! _options['popup'] ) {
				me.load_popup();
			} else {
				me.have_popup = true;
				me.data = _options['popup'];
				me.exec_scripts();
				me.prepare();
			}
		};

		/**
		 * If the popup provides custom javascript code we execute it here.
		 * Custom script might be provided by rules that are executed in JS such
		 * as the window-width rule or on-click behavior.
		 */
		this.exec_scripts = function exec_scripts() {
			var fn;
			if ( undefined !== me.data.script ) {
				fn = new Function( 'me', me.data.script );
				fn( me );
			}
		};


		/*======================================*\
		==========================================
		==                                      ==
		==           HELPER FUNCTIONS           ==
		==                                      ==
		==========================================
		\*======================================*/


		// Get a cookie value.
		this.get_cookie = function get_cookie( name ) {
			var i, c, cookie_name, value,
				ca = document.cookie.split( ';' );

			if ( me.data && me.data.popup_id ) {
				cookie_name = name + '-' + me.data.popup_id + "=";
			} else {
				cookie_name = name + "=";
			}

			for ( i = 0; i < ca.length; i += 1 ) {
				c = ca[i];
				while ( c.charAt(0) === ' ' ) {
					c = c.substring( 1, c.length );
				}
				if (c.indexOf( cookie_name ) === 0 ) {
					return c.substring( cookie_name.length, c.length );
				}
			}
			return null;
		};

		// Saves the value into a cookie.
		this.set_cookie = function set_cookie( name, value, days ) {
			var date, expires, cookie_name;

			if ( _options['preview'] ) { return; }

			if ( ! isNaN( days ) ) {
				date = new Date();
				date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
				expires = "; expires=" + date.toGMTString();
			} else {
				expires = "";
			}

			if ( me.data && me.data.popup_id ) {
				cookie_name = name + '-' + me.data.popup_id;
			} else {
				cookie_name = name;
			}

			document.cookie = cookie_name + "=" + value + expires + "; path=/";
		};


		/*-----  Finished  ------*/

		// Only expose the "init" and "load" functions of the PopUp.
		return {
			init: me.init,
			load: me.load_popup,
			extend: me
		};
	};

	// Local variables used by popup_open() and popup_close()
	var po_status = 'closed',
		po_queue = [],
		po_current = null;

	/**
	 * Either opens the PopUp or enqueues it to be opened as soon as the current
	 * PopUp is closed.
	 */
	function popup_open( popup ) {
		if ( 'closed' === po_status ) {
			if ( popup._show() ) {
				po_current = popup;
				po_status = 'open';
			} else {
				// The PopUp could not be opened due to some error...
				popup_close( popup );
			}
		} else {
			po_queue[po_queue.length] = popup;
		}
	}

	/**
	 * Marks the specified PopUp as closed and opens the next enqueued PopUp.
	 */
	function popup_close( popup ) {
		po_status = 'closed';
		po_current = null;

		// PopUp was closed, check if there is another PopUp in open-queue.
		if ( po_queue.length > 0 ) {
			var item = po_queue.shift();
			popup_open( item );
		}
	}

	/**
	 * Load popup data via ajax.
	 * This function will create new Popup objects.
	 */
	function load_popups( options, id, data ) {
		var ajax_args, ajax_data,
			po_id = 0,
			thefrom = window.location,
			thereferrer = document.referrer,
			the_data = null;

		var handle_done = function handle_done( data ) {
			the_data = jQuery.extend( {}, options );
			the_data.popup = data;

			// This will create new Popup objects.
			initialize( the_data );
		};

		// Legacy: force_popover = load a popup_id by ID.
		if ( window.force_popover !== undefined ) {
			po_id = window.force_popover.toString();
		}

		// New way of specifying popup ID is via param: load(id)
		if ( id !== undefined ) {
			po_id = id.toString();
		}

		options['ajax_data'] = options['ajax_data'] || {};
		ajax_data = jQuery.extend( {}, options['ajax_data'] );

		ajax_data['action']      = 'inc_popup';
		ajax_data['do']          = options['do'];
		ajax_data['thefrom']     = thefrom.toString();
		ajax_data['thereferrer'] = thereferrer.toString();

		if ( po_id ) { ajax_data['po_id'] = po_id; }
		if ( data )  { ajax_data['data'] = data; }
		if ( options['preview'] ) { ajax_data['preview'] = true; }

		ajax_args = {
			url:           options['ajaxurl'],
			dataType:      'jsonp',
			jsonpCallback: 'po_data',
			data:          ajax_data,
			success: function( data ) {
				handle_done( data );
			},
			complete: function() {
				jQuery( document ).trigger( 'popup-load-done', [the_data] );
			}
		};
		return jQuery.ajax(ajax_args);
	}

	/**
	 * Create and initialize new Popup objects
	 */
	function initialize( popup_data ) {
		if ( undefined === popup_data ) { return; }

		// Initlize a single PopUp.
		var spawn_popup = function spawn_popup( data ) {
			if ( undefined === data ) { return; }

			// Insert the PopUp CSS and HTML as hidden elements into the DOM.
			if ( undefined !== data.popup && undefined !== data.popup['html'] ) {
				jQuery( '<style type="text/css">' + data.popup['styles'] + '</style>' )
					.appendTo('head');

				jQuery( data.popup['html'] )
					.appendTo('body')
					.hide();
			}

			// inc_popup .. the most recently created Popup object.
			window.inc_popup = new Popup( data );

			// inc_popups .. collection of all Popup objects on current page.
			window.inc_popups[window.inc_popups.length] = window.inc_popup;

			jQuery( document ).trigger( 'popup-initialized', [window.inc_popup] );

			if ( data['noinit'] || data['preview'] ) { return; }
			window.inc_popup.init();
		};

		// Initialize a single or multiple PopUps, depending on provided data.
		if ( popup_data.popup instanceof Array ) {
			for ( var i = 0; i < popup_data.popup.length; i+= 1 ) {
				var data = jQuery.extend( {}, popup_data );
				data.popup = popup_data.popup[i];
				spawn_popup( data );
			}
		} else if ( popup_data instanceof Object ) {
			spawn_popup( popup_data );
		}
	}

	// Initialize the PopUp one the page is loaded.
	jQuery(function() {
		window.inc_popups = [];
		initialize( _popup_data );
	});

})();
