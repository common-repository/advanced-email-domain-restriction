<?php
/**
 * Main Class for the plugin
 *
 * @package aedr
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AEDR_Admin' ) ) {
	/**
	 * Class AEDR_Admin
	 *
	 * This class handles email domain restrictions for user registrations.
	 * It allows admins to set allowed email domains.
	 *
	 * @since 1.0.0
	 */
	class AEDR_Admin {

		/**
		 * Constructor.
		 *
		 * Initializes the class by registering hooks for admin settings.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'aedr_add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'aedr_settings_init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'aedr_enqueue_admin_scripts' ) );
		}

		/**
		 * Add admin menu.
		 *
		 * Adds a menu item for the plugin settings page.
		 *
		 * @since 1.0.0
		 */
		public function aedr_add_admin_menu() {
			add_options_page( 'Advanced Email Domain Restriction', 'Domain Restriction', 'manage_options', 'advanced-email-domain-restriction', array( $this, 'aedr_options_page' ) );
		}

		/**
		 * Run the main functionality.
		 *
		 * Hooks into various registration processes to enforce email domain restrictions.
		 *
		 * @since 1.0.0
		 * @since 1.1.0  woocommerce action added
		 */
		public function run() {
			add_filter( 'registration_errors', array( $this, 'aedr_check_email_domain' ), 10, 3 );
			add_action( 'woocommerce_registration_errors', array( $this, 'aedr_check_woocommerce_email_domain' ), 10, 3 );
		}

		/**
		 * Initialize settings.
		 *
		 * Registers the settings, sections, and fields for the plugin.
		 *
		 * @since 1.0.0
		 */
		public function aedr_settings_init() {

			register_setting( 'aedr_options', 'aedr_settings', array( $this, 'aedr_validate_settings' ) );

			add_settings_section( 'aedr_section', __( 'Domain Settings', 'advanced-email-domain-restriction' ), array( $this, 'aedr_settings_section_callback' ), 'aedr_options' );

			// Add fields for allowed domains.
			add_settings_field(
				'aedr_allowed_domains',
				__( 'Allowed Domains', 'advanced-email-domain-restriction' ),
				array( $this, 'aedr_render_entry_table' ),
				'aedr_options',
				'aedr_section',
				array(
					'field' => 'aedr_allowed_domains',
					'label' => __( 'Domain', 'advanced-email-domain-restriction' ),
				)
			);

			// Add field for custom error messages.
			add_settings_field( 'aedr_custom_error_message_allowed', __( 'Custom Error Message for Allowed Domains', 'advanced-email-domain-restriction' ), array( $this, 'aedr_render_text_field' ), 'aedr_options', 'aedr_section', array( 'field' => 'aedr_custom_error_message_allowed' ) );
		}

		/**
		 * Render entry table.
		 *
		 * Renders a table for entering allowed domains.
		 *
		 * @param array $args Arguments for rendering the table.
		 *
		 * @since 1.0.0
		 */
		public function aedr_render_entry_table( $args ) {
			$options = get_option( 'aedr_settings', array() );
			$entries = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : array();

			?>
			<table class="aedr-form-table">
				<tbody id="aedr-<?php echo esc_attr( $args['field'] ); ?>-list">
					<?php foreach ( $entries as $key => $entry ) : ?>
						<tr>
							<td>
								<input type="text" name="aedr_settings[<?php echo esc_attr( $args['field'] ); ?>][]" value="<?php echo esc_attr( $entry ); ?>" placeholder="<?php echo esc_attr( $args['label'] ); ?>" required>
							</td>
							<td>
								<button type="button" class="button aedr-remove-entry"><?php esc_html_e( 'Remove', 'advanced-email-domain-restriction' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<button type="button" class="button aedr-add-entry" data-field="<?php echo esc_attr( $args['field'] ); ?>"><?php esc_html_e( 'Add Entry', 'advanced-email-domain-restriction' ); ?></button>
			<?php
		}

		/**
		 * Render text field.
		 *
		 * Renders a text field for custom error messages.
		 *
		 * @param array $args Arguments for rendering the text field.
		 *
		 * @since 1.0.0
		 */
		public function aedr_render_text_field( $args ) {
			$options = get_option( 'aedr_settings', array() );
			$value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : '';
			?>
			<textarea rows="5" cols="55" name="aedr_settings[<?php echo esc_attr( $args['field'] ); ?>]" placeholder="<?php esc_html_e( 'Custom message', 'advanced-email-domain-restriction' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php
		}

		/**
		 * Settings section callback.
		 *
		 * Outputs the description for the settings section.
		 *
		 * @since 1.0.0
		 */
		public function aedr_settings_section_callback() {
			echo esc_html__( 'Configure allowed domains and custom messages.', 'advanced-email-domain-restriction' );
		}

		/**
		 * Options page.
		 *
		 * Renders the options page for the plugin.
		 *
		 * @since 1.0.0
		 */
		public function aedr_options_page() {
			?>
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'aedr_options' );
				do_settings_sections( 'aedr_options' );
				submit_button();
				?>
			</form>
			<?php
		}

		/**
		 * Enqueue admin scripts and styles.
		 *
		 * @param string $hook The current admin page.
		 *
		 * @since 1.0.0
		 */
		public function aedr_enqueue_admin_scripts( $hook ) {
			if ( 'settings_page_advanced-email-domain-restriction' !== $hook ) {
				return;
			}

			wp_enqueue_script( 'aedr-admin-script', AEDR_ROOT_URL . 'assets/js/aedr-admin.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'aedr-admin-style', AEDR_ROOT_URL . 'assets/css/aedr-admin.css', array(), '1.0.0' );

			$localized_strings = array(
				'aedr_remove_button_text'       => __( 'Remove', 'advanced-email-domain-restriction' ),
				'aedr_placeholder_domain_text'  => __( 'Domain: gmail.com', 'advanced-email-domain-restriction' ),
				'aedr_placeholder_message_text' => __( 'Custom message', 'advanced-email-domain-restriction' ),
			);

			wp_localize_script( 'aedr-admin-script', 'aedrLocalized', $localized_strings );
		}

		/**
		 * Check email domain during registration.
		 *
		 * Validates the email domain during standard registration.
		 *
		 * @param WP_Error $errors The registration errors.
		 * @param string   $user_email The user's email.
		 *
		 * @return WP_Error The modified registration errors.
		 *
		 * @since 1.0.0
		 */
		public function aedr_check_email_domain( $errors, $user_email ) {
			return $this->aedr_validate_email_domain( $errors, $user_email );
		}

		/**
		 * Check WooCommerce email domain during registration.
		 *
		 * Validates the email domain during WooCommerce registration.
		 *
		 * @param WP_Error $errors The registration errors.
		 * @param string   $user_email The user's email.
		 *
		 * @return WP_Error The modified registration errors.
		 *
		 * @since 1.1.0
		 */
		public function aedr_check_woocommerce_email_domain( $errors, $user_email ) {
			return $this->aedr_validate_email_domain( $errors, $user_email );
		}

		/**
		 * Validate settings.
		 *
		 * Validates the settings input before saving.
		 *
		 * @param array $input The input settings.
		 *
		 * @return array The validated settings.
		 *
		 * @since 1.0.0
		 */
		public function aedr_validate_settings( $input ) {
			$validated_input = array();

			// Validate allowed domains.
			if ( isset( $input['aedr_allowed_domains'] ) ) {
				$validated_input['aedr_allowed_domains'] = array_map( 'sanitize_text_field', $input['aedr_allowed_domains'] );
			}

			// Validate custom error messages.
			if ( isset( $input['aedr_custom_error_message_allowed'] ) ) {
				$validated_input['aedr_custom_error_message_allowed'] = sanitize_text_field( $input['aedr_custom_error_message_allowed'] );
			}

			return $validated_input;
		}

		/**
		 * Validate email domain.
		 *
		 * Checks if the email domain is allowed.
		 *
		 * @param WP_Error $errors The registration errors.
		 * @param string   $user_email The user's email.
		 *
		 * @return WP_Error The modified registration errors.
		 *
		 * @since 1.0.0
		 */
		private function aedr_validate_email_domain( $errors, $user_email ) {
			$options                      = get_option( 'aedr_settings', array() );
			$allowed_domains              = isset( $options['aedr_allowed_domains'] ) ? $options['aedr_allowed_domains'] : array();
			$custom_error_message_allowed = isset( $options['aedr_custom_error_message_allowed'] ) && ! empty( $options['aedr_custom_error_message_allowed'] ) ? $options['aedr_custom_error_message_allowed'] : __( 'Your email domain is not allowed.', 'advanced-email-domain-restriction' );
			$email_domain                 = substr( strrchr( $user_email, '@' ), 1 );

			if ( ! empty( $allowed_domains ) && ! in_array( $email_domain, $allowed_domains, true ) ) {
				$errors->add( 'aedr_domain_restriction_error', $custom_error_message_allowed );
			}

			return $errors;
		}
	}
}
