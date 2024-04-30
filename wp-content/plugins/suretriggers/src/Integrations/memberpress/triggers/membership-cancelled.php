<?php
/**
 * MembershipCancelled.
 * php version 5.6
 *
 * @category MembershipCancelled
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Triggers;

use MeprTransaction;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\MemberPress\MemberPress;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'MembershipCancelled' ) ) :

	/**
	 * MembershipCancelled
	 *
	 * @category PurchaseMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class MembershipCancelled {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MemberPress';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'mepr_subscription_transition_status';

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
				'label'         => __( 'Membership Cancelled', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 * This will trigger for both recurring and non-recurring transactions.
		 *
		 * @param string $old_status old status.
		 * @param string $new_status new status.
		 * @param object $sub subscription.
		 * @return void
		 */
		public function trigger_listener( $old_status, $new_status, $sub ) {
			if ( 'cancelled' !== (string) $new_status ) {
				return;
			}   
			$membership = MemberPress::get_subscription_context( $sub );
			$context    = array_merge(
				WordPress::get_user_context( $membership['user_id'] ),
				$membership
			);
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
	MembershipCancelled::get_instance();

endif;
