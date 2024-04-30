<?php
/**
 * CustomerCreated.
 * php version 5.6
 *
 * @category CustomerCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use WC_Customer;

/**
 * CustomerCreated
 *
 * @category CustomerCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CustomerCreated {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WooCommerce';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wc_customer_created';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {
		$triggers[ $this->integration ][ $this->trigger ] = [
			'event_name'    => 'woocommerce_thankyou',
			'label'         => __( 'Customer Created', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'woocommerce_thankyou',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param string $order_id order ID.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $order_id ) {
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
			
		if ( ! $order ) {
			return;
		}

		if ( is_object( $order ) && method_exists( $order, 'get_customer_id' ) ) {
			$customer = new WC_Customer( $order->get_customer_id() );
			if ( $customer->get_order_count() > 1 ) {
				return;
			}
			$last_order    = $customer->get_last_order();
			$customer_data = [
				'id'               => $customer->get_id(),
				'email'            => $customer->get_email(),
				'first_name'       => $customer->get_first_name(),
				'last_name'        => $customer->get_last_name(),
				'username'         => $customer->get_username(),
				'last_order_id'    => is_object( $last_order ) ? $last_order->get_id() : null,
				'orders_count'     => $customer->get_order_count(),
				'total_spent'      => wc_format_decimal( $customer->get_total_spent(), 2 ),
				'avatar_url'       => $customer->get_avatar_url(),
				'billing_address'  => [
					'first_name' => $customer->get_billing_first_name(),
					'last_name'  => $customer->get_billing_last_name(),
					'company'    => $customer->get_billing_company(),
					'address_1'  => $customer->get_billing_address_1(),
					'address_2'  => $customer->get_billing_address_2(),
					'city'       => $customer->get_billing_city(),
					'state'      => $customer->get_billing_state(),
					'postcode'   => $customer->get_billing_postcode(),
					'country'    => $customer->get_billing_country(),
					'email'      => $customer->get_billing_email(),
					'phone'      => $customer->get_billing_phone(),
				],
				'shipping_address' => [
					'first_name' => $customer->get_shipping_first_name(),
					'last_name'  => $customer->get_shipping_last_name(),
					'company'    => $customer->get_shipping_company(),
					'address_1'  => $customer->get_shipping_address_1(),
					'address_2'  => $customer->get_shipping_address_2(),
					'city'       => $customer->get_shipping_city(),
					'state'      => $customer->get_shipping_state(),
					'postcode'   => $customer->get_shipping_postcode(),
					'country'    => $customer->get_shipping_country(),
				],
			];
			if ( is_object( $last_order ) && method_exists( $last_order, 'get_date_created' ) ) {
				$created_date = $last_order->get_date_created();
				if ( is_object( $created_date ) && method_exists( $created_date, 'getTimestamp' ) ) {
					$last_order_date                  = $created_date->getTimestamp();
					$customer_data['created_at']      = $last_order_date;
					$customer_data['last_order_date'] = $last_order_date;
				}
			}
			
			$context = $customer_data;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}
}

CustomerCreated::get_instance();
