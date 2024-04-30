<?php
/**
 * WpcwUserCompletesUnit.
 * php version 5.6
 *
 * @category WpcwUserCompletesUnit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPCourseware\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WpcwUserCompletesUnit
 *
 * @category WpcwUserCompletesUnit
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WpcwUserCompletesUnit {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPCourseware';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wpcw_user_completes_unit';

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
	 *
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Completes Unit', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpcw_user_completed_unit',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 3,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int    $user_id User ID.
	 * @param int    $unit_id Unit ID.
	 * @param object $parent Parent.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $unit_id, $parent ) {

		if ( empty( $user_id ) ) {
			return;
		}

		$context = WordPress::get_user_context( $user_id );
		if ( function_exists( 'wpcw_get_unit' ) ) {
			$unit = wpcw_get_unit( $unit_id );
			if ( is_object( $unit ) ) {
				$unit         = get_object_vars( $unit );
				$unit['name'] = get_the_title( $unit_id );
			}
			$context = array_merge( WordPress::get_user_context( $user_id ), $unit );
		}

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $user_id,
				'context'    => $context,
			]
		);
	}
}

WpcwUserCompletesUnit::get_instance();
