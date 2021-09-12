<?php

class Segment_For_Wp_By_In8_Io_Segment_Php_Lib {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;
	/**
	 * @var
	 */
	protected $settings;

	public function __construct( $plugin_name, $version, $settings ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = $settings;

	}

	/**
	 * Initialise Segment consumer
	 */
	public function init_segment() {

		class_alias( 'Segment', 'Analytics' );

		if ( $this->settings["segment_php_consumer"] == 'socket' ) {

			$timeout = $this->settings["segment_php_consumer_timeout"];

			if ( ! is_numeric( $timeout ) ) {
				$timeout = 1;
			}

			Segment::init( $this->settings["php_api_key"], array(
				"consumer"      => "socket",
				"timeout"       => $timeout,
				"debug"         => false,
				"error_handler" => function ( $code, $msg ) {
					error_log( $msg );
				}
			) );

		} else {
			Segment::init( $this->settings["php_api_key"], array(
				"consumer" => "file",
				"filename" => plugin_dir_path( dirname( __FILE__ ) ) . 'tmp/analytics.log'
			) );
		}

	}

	public function file_consumer() {
		$settings = $this->settings;
		$args     = array(
			"secret"    => $settings["php_api_key"],
			"file"      => plugin_dir_path( dirname( __FILE__ ) ) . 'tmp/analytics.log',
			"send_file" => plugin_dir_path( dirname( __FILE__ ) ) . '/includes/segment_php/send.php',
		);
		if ( isset( $args["secret"] ) && isset( $args["file"] ) ) {
			include( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/segment_php/send.php' );
		}
	}

	/**
	 * @param ...$args 'wp user id'
	 */
	public function register_new_user( ...$args ) {
		$action             = current_action();
		$wp_user_id         = $args[0];
		$args['wp_user_id'] = $wp_user_id;
		$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		$event_name         = Segment_For_Wp_By_In8_Io::get_event_name( $action );
		$properties         = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		if ( $user_id && $event_name ) {
			Analytics::track( array(
				"userId"     => $user_id,
				"event"      => $event_name,
				"properties" => $properties
			) );
		}

		$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
		Analytics::identify( array(
			"userId" => $user_id,
			"traits" => $traits
		) );
		Analytics::flush();
	}

	/**
	 * @param ...$args 'two args, $user_login (username), $user (object)'
	 */
	public function wp_login( ...$args ) { //user

		$action             = current_action();
		$action_server      = $action . '_server';
		$args               = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$wp_user_id         = $args["args"][1]["ID"];
		$args['wp_user_id'] = $wp_user_id;
		$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		$event_name         = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties         = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		if ( $user_id && $event_name ) {
			Analytics::track( array(
				"userId"     => $user_id,
				"event"      => $event_name,
				"properties" => $properties
			) );
		}
		if ( Segment_For_Wp_By_In8_Io::check_associated_identify( 'hook', $action ) ) {
			$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
			Analytics::identify( array(
				"userId" => $user_id,
				"traits" => $traits
			) );

		}
		Analytics::flush();
	}

	/**
	 * @param ...$args 'one arg, the wp user id'
	 *
	 * @noinspection PhpUnused
	 */
	public function wp_logout( ...$args ) {
		$action             = current_action();
		$action_server      = $action . '_server';
		$args               = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$wp_user_id         = $args["args"][0];
		$args['wp_user_id'] = $wp_user_id;
		$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		$event_name         = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties         = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		if ( $user_id && $event_name ) {
			Analytics::track( array(
				"userId"     => $user_id,
				"event"      => $event_name,
				"properties" => $properties
			) );
			if ( Segment_For_Wp_By_In8_Io::check_associated_identify( 'hook', $action ) ) {
				$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
				Analytics::identify( array(
					"userId" => $user_id,
					"traits" => $traits
				) );
			}
		}

		Analytics::flush();
	}

	/**
	 * @param ...$args 'int $id, WP_Comment $comment'
	 */
	public function wp_insert_comment( ...$args ) {
		if ( isset( $args[1]->comment_author ) && $args[1]->comment_author == 'WooCommerce' ) {
			//because Woo inserts a comment with order details
			return;
		}
		$action        = current_action();
		$action_server = $action . '_server';
		$args          = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		if ( $args["args"][1]["user_id"] != 0 ) {
			$wp_user_id = $args["args"][1]["user_id"];
		} else {
			$wp_user_id = Segment_For_Wp_By_In8_Io::get_wp_user_id( $action_server, $args );
		}
		$args['wp_user_id'] = $wp_user_id;
		if ( !$wp_user_id ) {
			$anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		} else {
			$user_id = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		}
		$event_name = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		if ( $user_id && $event_name ) {
			Analytics::track( array(
				"userId"     => $user_id,
				"event"      => $event_name,
				"properties" => $properties
			) );
			if ( Segment_For_Wp_By_In8_Io::check_associated_identify( 'hook', $action ) ) {
				$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
				Analytics::identify( array(
					"userId" => $user_id,
					"traits" => $traits
				) );
			}
		}
		elseif ( $anon_id && $event_name ) {
			Analytics::track( array(
				"anonymousId" => $anon_id,
				"event"       => $event_name,
				"properties"  => $properties
			) );
		}
		Analytics::flush();

	}

	/**
	 * @param ...$args '$form_data'
	 */
	public function ninja_forms_after_submission( ...$args ) {
		$settings         = $this->settings;
		$action           = current_action();
		$args             = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$event_properties = array();
		//process fields
		foreach ( $args["args"][0]["fields"] as $field ) {
			if ( $field["value"] != "" ) {
				if ( $field["admin_label"] == $settings["track_ninja_forms_fieldset"]["ninja_forms_event_name_field"] ) {
					$args['event_name'] = sanitize_text_field( $field["value"] );
				}
				if ( $field["admin_label"] == $settings["track_ninja_forms_fieldset"]["ninja_forms_wp_user_id_field"] ) {
					$wp_user_id         = sanitize_text_field( $field["value"] );
					$args['wp_user_id'] = $wp_user_id;
					if ( $settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes' && $settings["track_ninja_forms_fieldset"]["ninja_forms_wp_user_id_field"] != '' ) {
						$args['nf_wp_user_id'] = $wp_user_id;
					}
				}
				if ( array_key_exists( 'ninja_form_event_properties', $settings["track_ninja_forms_fieldset"] ) ) {
					if ( count( $settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"] ) > 0 ) {
						$ninja_form_event_properties = $settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"];
						foreach ( $ninja_form_event_properties as $event_property ) {
							if ( $field["admin_label"] == $event_property["ninja_form_event_property_field_id"] ) {

								$event_properties[ $event_property["ninja_form_event_property_label"] ] = $field["value"];
							}
						}

					}
				}
				$args['properties'] = $event_properties;
			}

		}
		unset( $args["args"] );
		$event_name = $args['event_name'];
		$properties = array_filter( $args['properties'] );

		if ( ! isset( $args["wp_user_id"] ) ) {
			if ( is_user_logged_in() ) {
				$wp_user_id = get_current_user_id();
				$args["wp_user_id"] = $wp_user_id;
				$user_id = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
			}
			else {
				$anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
				$args['$anon_id'] = $anon_id;
				Analytics::track( array(
					"anonymousId" => $anon_id,
					"event"       => $event_name,
					"properties"  => $properties
				) );

			}
		}
		else {
			$wp_user_id = $args["wp_user_id"];
			$user_id = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		}
		if ( $user_id && $event_name ) {
			Analytics::track( array(
				"userId"     => $user_id,
				"event"      => $event_name,
				"properties" => $properties
			) );
		}
		if ( $settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes' && $wp_user_id ) {
			$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
			Analytics::identify( array(
				"userId" => $user_id,
				"traits" => $traits
			) );
		}

		Analytics::flush();

	}

	/**
	 * @param ...$args '$entry, $form '
	 */
	public function gform_after_submission( ...$args ) {

		$settings            = $this->settings;
		$action              = current_action();
		$args                = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$entry               = $args["args"][0];
		$form                = $args["args"][1];
		$gf_event_name_field = sanitize_text_field( $settings["track_gravity_forms_fieldset"]["gravity_forms_event_name_field"] );
		$gf_wp_user_id_field = sanitize_text_field( $settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"] );
		$gf_event_props      = array();

		foreach ( $form['fields'] as $field ) {
			if ( $gf_event_name_field != '' ) {
				if ( $field["adminLabel"] == $gf_event_name_field ) {
					$gf_event_name = rgar( $entry, $field["id"] );
					if ( $gf_event_name != '' ) {
						$args['event_name'] = sanitize_text_field( $gf_event_name );
					}
				}
				if ( $settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"] != '' ) {
					if ( $field["adminLabel"] == $gf_wp_user_id_field ) {
						$gf_wp_user_id         = rgar( $entry, $field["id"] );
						$args['gf_wp_user_id'] = sanitize_text_field( $gf_wp_user_id );
						$user                  = get_userdata( $gf_wp_user_id );
						if ( $user === false ) {
							$wp_user_id         = 0;
							$args['wp_user_id'] = 0;
							//user id does not exist
						} else {
							$wp_user_id         = $user->ID;
							$args['wp_user_id'] = $wp_user_id;
						}

					}
				}

				if ( array_key_exists( 'gravity_form_event_properties', $settings["track_gravity_forms_fieldset"] ) && count( $settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"] ) > 0 ) {
					foreach ( $settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"] as $property ) {
						if ( $property["gravity_form_event_property_field_id"] != '' ) {
							$gf_field_label_key = $property["gravity_form_event_property_field_id"];
							$gf_label_text      = $property["gravity_form_event_property_label"];
							if ( $field["adminLabel"] == $gf_field_label_key ) {
								$gf_field_id = $field["id"];

								$value = $entry[ $gf_field_id ];

								if ( $value && $value != '' ) {
									$gf_event_props[ sanitize_text_field( $gf_label_text ) ] = sanitize_text_field( $value );
								}

							}
						}


					}

				}

			}

		}

		unset( $args["args"] );
		$args['properties'] = $gf_event_props;

		if ( ! isset( $args["wp_user_id"] ) ) {
			if ( is_user_logged_in() ) {
				$wp_user_id         = sanitize_text_field( get_current_user_id() );
				$args["wp_user_id"] = $wp_user_id;
			}
		}
		if ( $args["wp_user_id"] == 0 || $args["wp_user_id"] = null || $args["wp_user_id"] = '' || ! isset( $args["wp_user_id"] ) ) {
			$anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		} else {
			$user_id = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		}
		$event_name = $args['event_name'];
		$properties = array_filter( $args['properties'] );
		if ( $user_id && $event_name ) {
			$trackable = Segment_For_Wp_By_In8_Io::check_trackable_user( $user );
			if ( $trackable ) {
				Analytics::track( array(
					"userId"     => $user_id,
					"event"      => $event_name,
					"properties" => $properties
				) );
				Analytics::flush();


				if ( $settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes' && $user_id && $wp_user_id ) {
					$traits = Segment_For_Wp_By_In8_Io::get_user_traits( $wp_user_id );
					Analytics::identify( array(
						"userId" => $user_id,
						"traits" => $traits
					) );
					Analytics::flush();

				}

			}

		} elseif ( ! $user_id && $event_name && $anon_id ) {
			Analytics::track( array(
				"anonymousId" => $anon_id,
				"event"       => $event_name,
				"properties"  => $properties
			) );
			Analytics::flush();
		}

	}

	/**
	 * When items are added
	 *
	 * @param ...$args
	 * $args[0]=$cart_item_key,
	 * $args[1]=$product_id,$
	 * args[2]=$quantity,
	 * $args[3]=$variation_id,
	 * $args[4]=$variation,
	 * $args[5]=$cart_item_data
	 */
	public function woocommerce_add_to_cart( ...$args ) {
		$action             = current_action();
		$action_server      = $action . '_server';
		$args               = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$args['product_id'] = $args["args"][1];
		if ( is_user_logged_in() ) {
			$wp_user_id         = get_current_user_id();
			$args['wp_user_id'] = $wp_user_id;
			$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		} else {
            $user_id = null;
			$anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		}
		$event_name             = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties             = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		$properties["quantity"] = $args["args"][2];
		$properties             = array_filter( $properties );
		if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id ) && $user_id ) {
				Analytics::track( array(
					"userId"     => $user_id,
					"event"      => $event_name,
					"properties" => $properties
				) );
			}
            elseif ( isset($anon_id) && $anon_id ) {
				Analytics::track( array(
					"anonymousId" => $anon_id,
					"event"       => $event_name,
					"properties"  => $properties
				) );
			}
            Analytics::flush();

        }

	}

	/**
	 * When items are removed
	 *
	 * @param ...$args
	 */
	public function woocommerce_cart_item_removed( ...$args ) {
		$action             = current_action();
		$action_server      = $action . '_server';
		$args               = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$args['product_id'] = $args["args"][1];
		if ( is_user_logged_in() ) {
			$wp_user_id         = get_current_user_id();
			$args['wp_user_id'] = $wp_user_id;
			$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		} else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		}

		$event_name             = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties             = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		$properties["quantity"] = $args["args"][2];
		$properties             = array_filter( $properties );
		if ( $event_name && $event_name !== '' ) {
			if ( isset($user_id ) && $user_id ) {
				Analytics::track( array(
					"userId"     => $user_id,
					"event"      => $event_name,
					"properties" => $properties
				) );
			}
            elseif ( isset($anon_id ) && $anon_id) {
				Analytics::track( array(
					"anonymousId" => $anon_id,
					"event"       => $event_name,
					"properties"  => $properties
				) );
			}
            Analytics::flush();

        }

	}

	/**
	 * When quantity changes, Product Added or Removed logic
	 *
	 * @param ...$args
	 * $args[0]=$cart_item_key,
	 * $args[1]=$quantity,
	 * $args[2]=$old_quantity,
	 * $args[3]=$cart
	 */
	public function woocommerce_after_cart_item_quantity_update( ...$args ) {

		if ( ! array_key_exists( '_wp_http_referer', $_REQUEST ) ) {
			return;
		}
		$cart_path    = parse_url( wc_get_cart_url(), PHP_URL_PATH );
		$request_path = parse_url( $_REQUEST["_wp_http_referer"], PHP_URL_PATH );
		if ( $cart_path !== $request_path ) {
			return;
		}

		$settings   = $this->settings;
		$action     = current_action();
		$args       = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$properties = array();

		$new_quantity       = $args["args"][1];
		$old_quantity       = $args["args"][2];
		$args["quantity"]   = abs( $new_quantity - $old_quantity );
		$cart               = $args["args"][3]["cart_contents"];
		$args["cart"]       = $cart;
		$item_key           = $args["args"][0];
		$args["item_key"]   = $item_key;
		$product_id         = $cart[ $item_key ]["product_id"];
		$args["product_id"] = $product_id;
		//if increased, product added
		if ( is_user_logged_in() ) {
			$wp_user_id         = get_current_user_id();
			$args['wp_user_id'] = $wp_user_id;
			$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		} else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		}

		//PRODUCT ADDED
		if ( $new_quantity > $old_quantity ) {
			$event_name = Segment_For_Wp_By_In8_Io::get_event_name( 'woocommerce_add_to_cart_server' );
			$properties = Segment_For_Wp_By_In8_Io::get_event_properties( 'woocommerce_after_cart_item_quantity_update', $args );
		}

		//PRODUCT REMOVED
		if ( $new_quantity < $old_quantity && $this->settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_removed_server"] == 'yes' ) {
			$event_name = Segment_For_Wp_By_In8_Io::get_event_name( 'woocommerce_remove_cart_item_server' );
			$properties = Segment_For_Wp_By_In8_Io::get_event_properties( 'woocommerce_remove_cart_item', $args );
		}

		if ( $event_name && $event_name !== '' ) {

            if ( isset($user_id ) && $user_id ) {

            {
				Analytics::track( array(
					"userId"     => $user_id,
					"event"      => $event_name,
					"properties" => $properties
				) );
			}
            }

            elseif ( isset($anon_id ) && $anon_id) {
				Analytics::track( array(
					"anonymousId" => $anon_id,
					"event"       => $event_name,
					"properties"  => $properties
				) );
			}
		}
        Analytics::flush();

    }

	/**
	 * When 'undo' after removing item from cart
	 *
	 * @internal
	 */
	public function woocommerce_cart_item_restored( ...$args ) {
		// args $removed_cart_item_key, $cart
		$action                = current_action();
		$action_server         = $action . '_server';
		$args                  = array(
			'action_hook' => $action,
			'args'        => json_decode( json_encode( func_get_args() ), true )
		);
		$item_key              = $args["args"][0];
		$args['cart_contents'] = $args["args"][1]["cart_contents"];
		$args['product_id']    = $args['cart_contents'][ $item_key ]["product_id"];

		if ( is_user_logged_in() ) {
			$wp_user_id         = get_current_user_id();
			$args['wp_user_id'] = $wp_user_id;
			$user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
		} else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
			$args['$anon_id'] = $anon_id;
		}
		$event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
		$properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
		$properties ["quantity"] = $args['cart_contents'][ $item_key ]["quantity"];
		$properties              = array_filter( $properties );
		if ( $event_name && $event_name !== '' ) {
			if ( isset($user_id) && $user_id ) {
				Analytics::track( array(
					"userId"     => $user_id,
					"event"      => $event_name,
					"properties" => $properties
				) );
			}
            elseif (isset($anon_id) && $anon_id ) {
				Analytics::track( array(
					"anonymousId" => $anon_id,
					"event"       => $event_name,
					"properties"  => $properties
				) );

			}
            Analytics::flush();
		}

	}

	/**
	 * @param ...$args 'order id'
	 */
	public function woocommerce_order_status_pending( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

	}

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_failed( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_processing( ...$args ) {
        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_completed( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_payment_complete( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_on_hold( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_refunded( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_cancelled( ...$args ) {

        $action                = current_action();
        $action_server         = $action . '_server';
        $args                  = array(
            'action_hook' => $action,
            'args'        => json_decode( json_encode( func_get_args() ), true )
        );

        $order_id              = $args["args"][0];
        $args['order_id'] = $order_id;

        if ( is_user_logged_in() ) {
            $wp_user_id         = get_current_user_id();
            $args['wp_user_id'] = $wp_user_id;
            $user_id            = Segment_For_Wp_By_In8_Io::get_user_id( $wp_user_id );
        }
        else {
            $user_id = null;
            $anon_id          = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
            $args['$anon_id'] = $anon_id;
        }

        $event_name              = Segment_For_Wp_By_In8_Io::get_event_name( $action_server );
        $properties              = Segment_For_Wp_By_In8_Io::get_event_properties( $action, $args );
        $properties              = array_filter( $properties );

        if(!is_user_logged_in()){
            if(array_key_exists('wc_user_id', $properties)){
                $user_id = $properties['wc_user_id'];
                unset($properties['wc_user_id']);
            }

        }

        if ( $event_name && $event_name !== '' ) {
            if ( isset($user_id) && $user_id ) {
                Analytics::track( array(
                    "userId"     => $user_id,
                    "event"      => $event_name,
                    "properties" => $properties
                ) );
            }
            elseif (isset($anon_id) && $anon_id ) {
                Analytics::track( array(
                    "anonymousId" => $anon_id,
                    "event"       => $event_name,
                    "properties"  => $properties
                ) );

            }
            Analytics::flush();
        }

    }

	public function custom_events (...$args) {

	}

}