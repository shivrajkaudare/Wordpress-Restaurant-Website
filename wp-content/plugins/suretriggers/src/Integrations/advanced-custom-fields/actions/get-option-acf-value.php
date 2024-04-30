<?php
/**
 * GetOptionAcfValue.
 * php version 5.6
 *
 * @category GetOptionAcfValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * GetOptionAcfValue
 *
 * @category GetOptionAcfValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetOptionAcfValue extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AdvancedCustomFields';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'get_option_acf_value';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Option Value', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$field_id = $selected_options['field_id'];

		if ( ! function_exists( 'get_field' ) ) {
			throw new Exception( 'Advanced Custom Fields get_field() function not found.' );
		}

		$get_value = get_field( $field_id, 'option' );
		if ( $get_value ) {
			if ( is_array( $get_value ) ) {
				if ( isset( $get_value[0] ) && is_array( $get_value[0] ) ) {
					$context[ $field_id ] = wp_json_encode( $get_value );
				} else {
					$context = $get_value;
				}
			} else {
				$context[ $field_id ] = $get_value;
			}
			return $context;
		} else {
			throw new Exception( 'Custom Field value not found.' );
		}
	}
}

GetOptionAcfValue::get_instance();
