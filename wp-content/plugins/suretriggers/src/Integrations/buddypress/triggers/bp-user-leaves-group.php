<?php
/**
 * BpUserLeavesGroup.
 * php version 5.6
 *
 * @category BpUserLeavesGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'BpUserLeavesGroup' ) ) :

	/**
	 * BpUserLeavesGroup
	 *
	 * @category BpUserLeavesGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class BpUserLeavesGroup {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyPress';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'bp_user_leaves_group';

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
				'label'         => __( 'A user leaves a group', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groups_leave_group',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $group_id Group ID.
		 * @param int $user_id User ID.
		 * @return void
		 */
		public function trigger_listener( $group_id, $user_id ) {
			
			if ( function_exists( 'groups_get_group' ) ) {
				$group = groups_get_group( $group_id );
				if ( is_object( $group ) ) {
					$group = get_object_vars( $group );
				}
				$context['group']    = $group;
				$context['bp_group'] = $group_id;
				$context['user']     = WordPress::get_user_context( $user_id );

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	BpUserLeavesGroup::get_instance();

endif;
