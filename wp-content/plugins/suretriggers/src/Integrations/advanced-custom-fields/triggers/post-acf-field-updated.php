<?php
/**
 * PostAcfFieldUpdated.
 * php version 5.6
 *
 * @category PostAcfFieldUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedCustomFields\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'PostAcfFieldUpdated' ) ) :

	/**
	 * PostAcfFieldUpdated
	 *
	 * @category PostAcfFieldUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PostAcfFieldUpdated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AdvancedCustomFields';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'post_acf_field_updated';

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
				'label'         => __( 'Field Updated On Post', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'acf/save_post',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $post_id  Post ID.
		 * @return void|bool
		 */
		public function trigger_listener( $post_id ) {

			if ( ! is_int( $post_id ) ) {
				return;
			}

            $post_data = $_POST; // @codingStandardsIgnoreLine

			// Check and update $_POST data.
			if ( $post_data['acf'] ) {
				if ( function_exists( 'get_fields' ) ) {
					$fields = get_fields( $post_id );
					foreach ( $fields as $key => $field ) {
						$context['field_id'] = $key;
						if ( function_exists( 'get_field' ) ) {
							$context[ $key ] = get_field( $key, $post_id );
						}
					}
				}
			} else {
				return;
			}
			$context['post'] = WordPress::get_post_context( $post_id );
			
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
	PostAcfFieldUpdated::get_instance();

endif;
