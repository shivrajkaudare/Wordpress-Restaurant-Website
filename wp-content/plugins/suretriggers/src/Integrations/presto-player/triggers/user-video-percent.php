<?php
/**
 * UserVideoPercent.
 * php version 5.6
 *
 * @category UserVideoPercent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PrestoPlayer\Triggers;

use PrestoPlayer\Models\Video;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserVideoPercent' ) ) :

	/**
	 * UserVideoPercent
	 *
	 * @category UserVideoPercent
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserVideoPercent {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'PrestoPlayer';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_video_percent';

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
				'label'         => __( 'Video Watched', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'presto_player_progress',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $video_id The entry that was just created.
		 * @param int $percent The current form.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $video_id, $percent ) {
			$user_id                        = ap_get_current_user_id();
			$context                        = WordPress::get_user_context( $user_id );
			$context['pp_video']            = $video_id;
			$context['pp_video_percentage'] = $percent;
			$context['video']               = ( new Video( $video_id ) )->toArray();

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
	UserVideoPercent::get_instance();

endif;
