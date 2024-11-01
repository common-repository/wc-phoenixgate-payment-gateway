<?php
/**
 * The payment gateway class.
 *
 * @link       https://wpspins.com
 * @since      1.0.0
 *
 * @package    Woo_Phxmn
 * @subpackage Woo_Phxmn/includes
 */

/**
 * Initialize payment gateway class.
 *
 * @return mixed
 */
function initialize_gateway_class() {
	/**
	 * The payment gateway class.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    Woo_Phxmn
	 * @subpackage Woo_Phxmn/includes
	 * @author     WPSPIN LLC <mike@wpxhouston.com>
	 */
	class Woo_Phxmn_Payment_Gateway extends WC_Payment_Gateway {
		/**
		 * Payment gateway constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'wc-phoenixgate-payment-gateway';
			$this->icon               = '';
			$this->has_fields         = true;
			$this->title              = __( 'PhoeniXGate Credit Cards', 'wc-phoenixgate-payment-gateway' );
			$this->method_title       = __( 'PhoeniXGate Credi Cards', 'wc-phoenixgate-payment-gateway' );
			$this->method_description = __( 'PhoeniXGate Credit Cards Payment Gateway for WooCommerce', 'wc-phoenixgate-payment-gateway' );
			// Supports.
			$this->supports = array( 'default_credit_card_form', 'refunds' );
			// load backend options fields.
			$this->init_form_fields();
			// load the settings.
			$this->init_settings();
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->enabled      = $this->get_option( 'enabled' );
			$this->test_mode    = 'yes' === $this->get_option( 'test_mode' );
			$this->debug_mode   = 'yes' === $this->get_option( 'debug_mode' );
			$this->base_api_url = $this->test_mode ? $this->get_option( 'test_api_url' ) : $this->get_option( 'api_url' );
			$this->private_key  = $this->test_mode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->publish_key  = $this->test_mode ? $this->get_option( 'test_publish_key' ) : $this->get_option( 'publish_key' );
			$this->logger       = wc_get_logger();
			// Action hook to saves the settings.
			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}
			// Add multisite-specific hook.
			if ( is_multisite() ) {
				add_action( 'update_option_' . $this->id . '_settings', array( $this, 'update_site_settings' ) );
			}
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		}
		/**
		 * Initialize the Form Fields.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'          => array(
					'title'       => __( 'Enable/Disable', 'wc-phoenixgate-payment-gateway' ),
					'label'       => __( 'Enable PhoeniXGate Credit Cards', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'checkbox',
					'description' => __( 'This enable the PhoeniXGate Credit Cards.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => 'no',
					'desc_tip'    => true,
				),
				'title'            => array(
					'title'       => __( 'Title', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => __( 'PhoeniXGate Credit Cards', 'wc-phoenixgate-payment-gateway' ),
					'desc_tip'    => true,
				),
				'description'      => array(
					'title'       => __( 'Description', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => __( 'Pay via our PhoeniXGate payment gateway.', 'wc-phoenixgate-payment-gateway' ),
				),
				'test_mode'        => array(
					'title'       => __( 'Test mode', 'wc-phoenixgate-payment-gateway' ),
					'label'       => __( 'Enable Test Mode', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'checkbox',
					'description' => __( 'The test mode of API.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'debug_mode'       => array(
					'title'       => __( 'Enable debug log', 'wc-phoenixgate-payment-gateway' ),
					'label'       => __( 'Enable debug log', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'checkbox',
					'description' => __( 'Enable Debug log to WooCommerce status > Logs to view API errors.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => 'no',
					'desc_tip'    => true,
				),
				'test_api_url'     => array(
					'title' => __( 'Test API URL', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'test_publish_key' => array(
					'title' => __( 'Test username', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'test_private_key' => array(
					'title' => __( 'Test password', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'password',
				),
				'api_url'          => array(
					'title' => __( 'Live API URL', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'publish_key'      => array(
					'title' => __( 'Live username', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'private_key'      => array(
					'title' => __( 'Live password', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'password',
				),
			);
		}
		/**
		 * Add wc log when debug mode is enabled.
		 *
		 * @param  mixed $message message to log.
		 * @return void
		 */
		private function error_log( $message ) {
			if ( $this->debug_mode ) {
				$this->logger->error( $message, array( 'source' => $this->id ) );
			}
		}
		/**
		 * Get API path by name.
		 *
		 * @param  string $name name of API path.
		 * @return string
		 */
		private function get_api_path( $name ) {
			$base_api_url = rtrim( $this->base_api_url, '/' ) . '/';
			$api_paths    = array(
				'auth' => 'Authenticate',
				'card' => 'v2/transactions/bcp',
			);
			$api_path     = $api_paths[ $name ];
			$full_path    = $base_api_url . $api_path;
			return add_query_arg( 'format', 'json', $full_path );
		}
		/**
		 * Get bearer token used in the transaction request.
		 *
		 * @return mixed
		 */
		private function get_bearer_token() {
			$response = wp_remote_get(
				add_query_arg(
					array(
						'UserName' => $this->publish_key,
						'Password' => $this->private_key,
					),
					$this->get_api_path( 'auth' ),
				),
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json',
					),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				return isset( $body['BearerToken'] ) ? $body['BearerToken'] : false;
			}
			if ( is_wp_error( $response ) ) {
				$this->error_log( $response->get_error_message() );
			} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				/* translators: %1$s: response code */
				$this->error_log( sprintf( __( 'Bearer token API reponse code is (%1$s) response (%2$s).', 'wc-phoenixgate-payment-gateway' ), wp_remote_retrieve_response_code( $response ), wc_print_r( $response, true ) ) );
			}
			return false;
		}
		/**
		 * Create new transaction.
		 *
		 * @param  array $args body args.
		 * @return mixed
		 */
		private function set_transaction_api( $args ) {
			$token = $this->get_bearer_token();
			if ( ! $token ) {
				return false;
			}
			$response = wp_remote_post(
				$this->get_api_path( 'card' ),
				array(
					'body'    => wp_json_encode( $args ),
					'timeout' => 10,
					'headers' => array(
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				return $body;
			}
			if ( 201 !== wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['ResponseMessage'] ) ) {
					$this->error_log( $body['ResponseMessage'] );
				}
			}
			if ( is_wp_error( $response ) ) {
				$this->error_log( $response->get_error_message() );
			}
			return false;
		}
		/**
		 * Refund a transaction.
		 *
		 * @param  array $args body args.
		 * @return mixed
		 */
		private function refund_transaction_api( $args ) {
			$token = $this->get_bearer_token();
			if ( ! $token ) {
				return false;
			}
			$response = wp_remote_post(
				$this->get_api_path( 'card' ),
				array(
					'body'    => wp_json_encode( $args ),
					'timeout' => 10,
					'headers' => array(
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) && 201 === wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				return $body;
			}
			if ( is_wp_error( $response ) ) {
				$this->error_log( $response->get_error_message() );
			}
			return false;
		}
		/**
		 * Payment form fields.
		 *
		 * @return void
		 */
		public function payment_fields() {
			if ( $this->description ) {
				if ( $this->test_mode ) {
					$this->description .= __( ' Test mode is enabled. You can use this dummy credit card number 4111111111111111 to test it.', 'wc-phoenixgate-payment-gateway' );
				}
				echo wp_kses_post( wpautop( $this->description ) );
			}
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/woo-phxmn-public-display.php';
		}
		/**
		 * Process payment.
		 *
		 * @param  mixed $order_id Order that processing.
		 * @return mixed
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
			$ccno   = isset( $_POST['woo_phxmn_ccno'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_phxmn_ccno'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$exdate = isset( $_POST['woo_phxmn_expdate'] ) ? str_replace( '/', '', sanitize_text_field( wp_unslash( $_POST['woo_phxmn_expdate'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cvcno  = isset( $_POST['woo_phxmn_cvc'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_phxmn_cvc'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// get order detailes.
			$order       = wc_get_order( $order_id );
			$amount      = $order->get_total();
			$args        = array(
				'TransactionType' => 'Sale',
				'ForceDuplicate'  => true,
				'CardData'        => array(
					'CardNumber'     => $ccno,
					'ExpirationDate' => $exdate,
					'CVV'            => $cvcno,
				),
				'InvoiceData'     => array(
					'TotalAmount' => $amount,
				),
			);
			$transaction = $this->set_transaction_api( $args );
			if ( $transaction ) {
				$transaction_id     = $transaction['TransactionId'];
				$transaction_status = $transaction['ResponseMessage'];
				$status_code        = $transaction['ResultCode'];
				if ( 0 === $status_code ) {
					$order->set_transaction_id( $transaction_id );
					$order->payment_complete();
					$order->reduce_order_stock();
					/* translators: %1$s: transaction ID */
					$order->add_order_note( sprintf( __( 'Payment transaction with ID (%1$s) was successfully completed.', 'wc-phoenixgate-payment-gateway' ), $transaction_id ), false );
					$woocommerce->cart->empty_cart();
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				}
				$order->add_order_note(
					/* translators: 1: transaction ID 2: transaction status */
					sprintf( __( 'Payment transaction with ID (%1$s) was not completed. Status: %2$s.', 'wc-phoenixgate-payment-gateway' ), $transaction_id, $transaction_status ),
					false
				);
				$this->error_log( __( 'Transaction wasn\'t completed. response:', 'wc-phoenixgate-payment-gateway' ) . wc_print_r( $transaction, true ) );
			}
			wc_add_notice( __( 'Transaction wasn\'t completed.', 'wc-phoenixgate-payment-gateway' ), 'error' );
		}
		/**
		 * Can the order be refunded via this gateway?
		 *
		 * @param  WC_Order $order Order object.
		 * @return bool
		 */
		public function can_refund_order( $order ) {
			return $order && $order->get_transaction_id() && ! empty( $this->base_api_url ) && ! empty( $this->private_key ) && ! empty( $this->publish_key );
		}
		/**
		 * Process a refund if supported.
		 *
		 * @param  int    $order_id Order ID.
		 * @param  float  $amount Refund amount.
		 * @param  string $reason Refund reason.
		 * @return bool
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order = wc_get_order( $order_id );
			if ( ! $this->can_refund_order( $order ) ) {
				return false;
			}
			$args   = array(
				'TransactionType'     => 'Refund',
				'ForceDuplicate'      => true,
				'OriginalTransaction' => array(
					'TransactionId' => $order->get_transaction_id(),
				),
				'InvoiceData'         => array(
					'TotalAmount' => $amount,
				),
			);
			$refund = $this->refund_transaction_api( $args );
			if ( $refund ) {
				$transaction_id     = $refund['TransactionId'];
				$transaction_status = $refund['ResponseMessage'];
				$result_code        = $refund['ResponseCode'];
				if ( 0 === $result_code ) {
					$order->add_order_note(
						/* translators: 1: Payment gateway title */
						sprintf( __( 'Amount %1$s was refunded successfully using %2$s.', 'wc-phoenixgate-payment-gateway' ), $amount, $this->title )
					);
					return true;
				}
				$this->error_log( __( 'Order wasn\'t refunded successfully. response:', 'wc-phoenixgate-payment-gateway' ) . wc_print_r( $refund, true ) );
			}
			/* translators: 1: Payment gateway title */
			sprintf( __( 'Order wasn\'t refunded successfully using %1$s.', 'wc-phoenixgate-payment-gateway' ), $this->title );
			return false;
		}
		/**
		 * Payment_scripts function.
		 *
		 * Outputs scripts used for stripe payment
		 */
		public function payment_scripts() {
			wp_enqueue_script( 'jquery-validator', WOO_PHXMN_PLUGIN_URL . '/public/js/jquery.validator.js', array( 'jquery' ), '1.2.0', true );
			wp_register_script( 'wc-phoenixgate-payment-gateway', WOO_PHXMN_PLUGIN_URL . '/public/js/woo-phxmn-public.js', array( 'jquery', 'jquery-validator' ), '1.0.0', true );
			wp_enqueue_script( 'wc-phoenixgate-payment-gateway' );
			wp_enqueue_style( 'wc-phoenixgate-payment-gateway', WOO_PHXMN_PLUGIN_URL . '/public/css/woo-phxmn-public.css', array(), '1.0.0', 'all' );
		}

		/**
		 * Update site settings for multisite installations.
		 *
		 * This method is triggered when the settings for the custom payment gateway
		 * are updated in the network admin. It ensures that the settings are updated
		 * for each individual site within the multisite network.
		 */
		public function update_site_settings() {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				// Your code to update settings for each site.
				restore_current_blog();
			}
		}
	}
	class Woo_Phxmn_Payment_Check extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'woo_phxmn_payment_check';
			$this->icon               = apply_filters( 'woocommerce_cheque_icon', '' );
			$this->has_fields         = false;
			$this->method_title       = __( 'PhoeniXGate Check Payment', 'wc-phoenixgate-payment-gateway' );
			$this->method_description = __( 'Allows payments by check.', 'wc-phoenixgate-payment-gateway' );
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
			// Define user set variables.
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->enabled      = $this->get_option( 'enabled' );
			$this->test_mode    = 'yes' === $this->get_option( 'test_mode' );
			$this->debug_mode   = 'yes' === $this->get_option( 'debug_mode' );
			$this->base_api_url = $this->test_mode ? $this->get_option( 'test_api_url' ) : $this->get_option( 'api_url' );
			$this->private_key  = $this->test_mode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->publish_key  = $this->test_mode ? $this->get_option( 'test_publish_key' ) : $this->get_option( 'publish_key' );
			$this->logger       = wc_get_logger();
			// Actions.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			// Add multisite-specific hook.
			if ( is_multisite() ) {
				add_action( 'update_option_' . $this->id . '_settings', array( $this, 'update_site_settings' ) );
			}
		}
		/**
		 * Initialize Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'      => array(
					'title'   => __( 'Enable/Disable', 'wc-phoenixgate-payment-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Check Payment', 'wc-phoenixgate-payment-gateway' ),
					'default' => 'yes',
				),
				'title'        => array(
					'title'       => __( 'Title', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => __( 'PhoeniXGate Check Payment', 'wc-phoenixgate-payment-gateway' ),
					'desc_tip'    => true,
				),
				'description'  => array(
					'title'       => __( 'Description', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => __( 'Please fill the blow.', 'wc-phoenixgate-payment-gateway' ),
					'desc_tip'    => true,
				),
				'test_mode'        => array(
					'title'       => __( 'Test mode', 'wc-phoenixgate-payment-gateway' ),
					'label'       => __( 'Enable Test Mode', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'checkbox',
					'description' => __( 'The test mode of API.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'debug_mode'       => array(
					'title'       => __( 'Enable debug log', 'wc-phoenixgate-payment-gateway' ),
					'label'       => __( 'Enable debug log', 'wc-phoenixgate-payment-gateway' ),
					'type'        => 'checkbox',
					'description' => __( 'Enable Debug log to WooCommerce status > Logs to view API errors.', 'wc-phoenixgate-payment-gateway' ),
					'default'     => 'no',
					'desc_tip'    => true,
				),
				'test_api_url'     => array(
					'title' => __( 'Test API URL', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'test_publish_key' => array(
					'title' => __( 'Test username', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'test_private_key' => array(
					'title' => __( 'Test password', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'password',
				),
				'api_url'          => array(
					'title' => __( 'Live API URL', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'publish_key'      => array(
					'title' => __( 'Live username', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'text',
				),
				'private_key'      => array(
					'title' => __( 'Live password', 'wc-phoenixgate-payment-gateway' ),
					'type'  => 'password',
				),
			);
		}
		/**
		 * Form of check payment.
		 */
		public function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wptexturize( $this->description ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			if ( $this->test_mode ) {
				echo __( ' Test mode is enabled. You can use this dummy check number 123456789 to test it.', 'wc-phoenixgate-payment-gateway' );
			}
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/woo-phxmn-check-display.php';

		}
		/**
		 * Add wc log when debug mode is enabled.
		 *
		 * @param  mixed $message message to log.
		 * @return void
		 */
		private function error_log( $message ) {
			if ( $this->debug_mode ) {
				$this->logger->error( $message, array( 'source' => $this->id ) );
			}
		}
		/**
		 * Get API path by name.
		 *
		 * @param  string $name name of API path.
		 * @return string
		 */
		private function get_api_path( $name ) {
			$base_api_url = rtrim( $this->base_api_url, '/' ) . '/';
			$api_paths    = array(
				'auth'  => 'Authenticate',
				'check' => 'v2/transactions/ecp',
			);
			$api_path     = $api_paths[ $name ];
			$full_path    = $base_api_url . $api_path;
			return add_query_arg( 'format', 'json', $full_path );
		}
		/**
		 * Get bearer token used in the transaction request.
		 *
		 * @return mixed
		 */
		private function get_bearer_token() {
			$response = wp_remote_get(
				add_query_arg(
					array(
						'UserName' => $this->publish_key,
						'Password' => $this->private_key,
					),
					$this->get_api_path( 'auth' ),
				),
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json',
					),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				return isset( $body['BearerToken'] ) ? $body['BearerToken'] : false;
			}
			if ( is_wp_error( $response ) ) {
				$this->error_log( $response->get_error_message() );
			} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				/* translators: %1$s: response code */
				$this->error_log( sprintf( __( 'Bearer token API reponse code is (%1$s) response (%2$s).', 'wc-phoenixgate-payment-gateway' ), wp_remote_retrieve_response_code( $response ), wc_print_r( $response, true ) ) );
			}
			return false;
		}
		/**
		 * Run check transaction API.
		 *
		 * @param  mixed $args request arguments.
		 * @return mixed
		 */
		private function run_check_transation_api( $args ) {
			$token = $this->get_bearer_token();
			if ( ! $token ) {
				return false;
			}
			$response = wp_remote_post(
				$this->get_api_path( 'check' ),
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $args ),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				return $body;
			}
			if ( is_wp_error( $response ) ) {
				$this->error_log( $response->get_error_message() );
			} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				/* translators: %1$s: response code */
				$this->error_log( sprintf( __( 'Check transaction API reponse code is (%1$s) response (%2$s).', 'wc-phoenixgate-payment-gateway' ), wp_remote_retrieve_response_code( $response ), wc_print_r( $response, true ) ) );
			}
			return false;
		}
		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id Order ID.
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			// Get the field values.
			$name_on_check  = isset( $_POST['woo_phxmn_check_name'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_phxmn_check_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$routing_number = isset( $_POST['woo_phxmn_check_routing'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_phxmn_check_routing'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$account_number = isset( $_POST['woo_phxmn_check_account'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_phxmn_check_account'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$total          = $order->get_total();
			$args           = array(
				'TransactionType' => 'Debit',
				'CheckData'       => array(
					'NameOnCheck'   => $name_on_check,
					'RoutingNumber' => $routing_number,
					'AccountNumber' => $account_number,
				),
				'InvoiceData'     => array(
					'TotalAmount' => $total,
				),

			);
			$result = $this->run_check_transation_api( $args );
			if ( ! $result ) {
				$order->add_order_note(
					sprintf(
						/* translators: %1$s: error message */
						__( 'Transaction failed: %1$s', 'wc-phoenixgate-payment-gateway' ),
						$result['ResponseMessage']
					)
				);
				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			}
			$status      = isset( $result['ResponseMessage'] ) ? $result['ResponseMessage'] : '';
			$status_code = isset( $result['ResultCode'] ) ? $result['ResultCode'] : '';
			if ( 0 === $status_code ) {
				$order->payment_complete( $result['TransactionId'] );
				$order->reduce_order_stock();
				WC()->cart->empty_cart();
				$order->add_order_note(
					sprintf(
						/* translators: %1$s: transaction id */
						__( 'Transaction ID: %1$s', 'wc-phoenixgate-payment-gateway' ),
						$result['TransactionId']
					)
				);
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}
			$order->add_order_note(
				sprintf(
					/* translators: %1$s: error message */
					__( 'Transaction failed: %1$s', 'wc-phoenixgate-payment-gateway' ),
					$result['ResponseMessage']
				)
			);
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
		/**
		 * Update site settings for multisite installations.
		 *
		 * This method is triggered when the settings for the custom payment gateway
		 * are updated in the network admin. It ensures that the settings are updated
		 * for each individual site within the multisite network.
		 */
		public function update_site_settings() {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				// Your code to update settings for each site.
				restore_current_blog();
			}
		}
	}
}
add_action( 'plugins_loaded', 'initialize_gateway_class' );
