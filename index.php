<?php
/**
 * Plugin Name:       Event Tickets Extension: Check-In User Role
 * Plugin URI:        https://theeventscalendar.com/extensions/checkin-user-role/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-checkin-user-role/
 * Description:       This extension will allow you to select from which user role level you will be able to check in ET tickets.
 * Version:           1.0.0dev
 * Extension Class:   Tribe__Extension__Checkin_User_Role
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-checkin-user-role
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( 'Tribe__Extension__Checkin_User_Role' )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Tribe__Extension__Checkin_User_Role extends Tribe__Extension {

		protected $opts_prefix = 'tribe_ext_checkin_user_role_';

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			// Requirements and other properties such as the extension homepage can be defined here.
			// Examples:
			$this->add_required_plugin( 'Tribe__Tickets_Plus__Main' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			// Don't forget to generate the 'languages/tribe-ext-checkin-user-role.pot' file
			load_plugin_textdomain( 'tribe-ext-checkin-user-role', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			/**
			 * Protect against fatals by specifying the required minimum PHP
			 * version. Make sure to match the readme.txt header.
			 * All extensions require PHP 5.6+, following along with https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/
			 *
			 * Delete this paragraph and the non-applicable comments below.
			 *
			 * Note that older version syntax errors may still throw fatals even
			 * if you implement this PHP version checking so QA it at least once.
			 *
			 * @link https://secure.php.net/manual/en/migration56.new-features.php
			 * 5.6: Variadic Functions, Argument Unpacking, and Constant Expressions
			 *
			 * @link https://secure.php.net/manual/en/migration70.new-features.php
			 * 7.0: Return Types, Scalar Type Hints, Spaceship Operator, Constant Arrays Using define(), Anonymous Classes, intdiv(), and preg_replace_callback_array()
			 *
			 * @link https://secure.php.net/manual/en/migration71.new-features.php
			 * 7.1: Class Constant Visibility, Nullable Types, Multiple Exceptions per Catch Block, `iterable` Pseudo-Type, and Negative String Offsets
			 *
			 * @link https://secure.php.net/manual/en/migration72.new-features.php
			 * 7.2: `object` Parameter and Covariant Return Typing, Abstract Function Override, and Allow Trailing Comma for Grouped Namespaces
			 */
			$php_required_version = '5.6';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if (
					is_admin()
					&& current_user_can( 'activate_plugins' )
				) {
					$message = '<p>';
					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', 'tribe-ext-checkin-user-role' ), $this->get_name(), $php_required_version );
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( $this->get_name(), $message, 'type=error' );
				}

				return;
			}

			add_action( 'admin_init', array( $this, 'add_settings' ) );
			//add_filter( 'tribe_tickets_caps_can_manage_attendees', array( $this, 'custom_checkin_role' ) );
			add_filter( 'tribe_tickets_plus_qr_handle_redirects', array( $this, 'custom_checkin_role' ), 10, 4 );
		}

		public function add_settings() {
			require_once dirname( __FILE__ ) . '/src/Tribe/Settings_Helper.php';

			$checkin_user_roles = array(
				'edit_posts' => 'Contributor (default)',
				'publish_posts' => 'Author',
				'publish_pages' => 'Editor',
				'manage_options' => 'Administrator',
			);

			if ( is_multisite() ) {
				$checkin_user_roles['manage_network'] = 'Super Admin';
			}

			$setting_helper = new Tribe__Settings_Helper();

			$fields = array(
				$this->opts_prefix . 'user_role' => array(
					'type'            => 'dropdown',
					'options'         => $checkin_user_roles,
					'label'           => esc_html__( 'Check-In user role', 'tribe-ext-checkin-user-role' ),
					'tooltip'         => esc_html__( 'Minimum user role required to check-in users.', 'tribe-ext-checkin-user-role' ),
					'validation_type' => 'html',
				),
			);

			$setting_helper->add_fields(
				$fields,
				'event-tickets',
				'tickets-enable-qr-codes',
				false
			);
		}

		/**
		 * Include a docblock for every class method and property.
		 */
		public function custom_checkin_role( $url, $event_id, $ticket_id, $user_had_access ) {
			$rur = tribe_get_option( $this->opts_prefix . 'user_role' );
			if ( ! current_user_can( $rur ) ) {
				$url = get_permalink( $event_id );
			}
			//apply_filters( 'tribe_tickets_plus_qr_handle_redirects', $url, $event_id, $ticket_id, $user_had_access );
			//$rur = tribe_get_option( $this->opts_prefix . 'user_role' );
			//$array = array( tribe_get_option( $this->opts_prefix . 'user_role' ) );
			//return $array;
			return $url;
		}

	} // end class
} // end if class_exists check
