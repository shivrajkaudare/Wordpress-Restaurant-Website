<?php
/**
 * OrderDeclinedByVendor.
 * php version 5.6
 *
 * @category OrderDeclinedByVendor
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'OrderDeclinedByVendor' ) ) :

	/**
	 * OrderDeclinedByVendor
	 *
	 * @category OrderDeclinedByVendor
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class OrderDeclinedByVendor {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Voxel';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'voxel_order_declined_by_vendor';

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
				'label'         => __( 'Order Declined By Vendor', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/orders/vendor:order_declined',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'order' ) || ! property_exists( $event, 'customer' ) ) {
				return;
			}
			$order                     = $event->order;
			$context['id']             = $order->get_id();
			$context['post_id']        = $order->get_post_id();
			$context['product_type']   = $order->product_type->get_key();
			$context['details']        = $order->get_details();
			$context['status']         = $order->get_status();
			$context['mode']           = $order->get_mode();
			$context['object_id']      = $order->get_object_id();
			$context['object_details'] = $order->get_object_details();
			$context['created_at']     = $order->get_created_at();
			$context['vendor']         = WordPress::get_user_context( $order->get_vendor_id() );
			$context['customer']       = WordPress::get_user_context( $event->customer->get_id() );
	
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	OrderDeclinedByVendor::get_instance();

endif;
