<?php
/**
 * AddPostUserActivityStream.
 * php version 5.6
 *
 * @category AddPostUserActivityStream
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use BP_Activity_Activity;

/**
 * AddPostUserActivityStream
 *
 * @category AddPostUserActivityStream
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostUserActivityStream extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'add_post_user_activity_stream';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( "Add a post to the user's activity stream", 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$action         = $selected_options['activity_action'];
		$action_link    = $selected_options['activity_action_link'];
		$action_content = $selected_options['activity_content'];
		$action_author  = $selected_options['wp_user_email'];

		if ( function_exists( 'bp_activity_add' ) ) {
			if ( is_email( $action_author ) ) {
				$user = get_user_by( 'email', $action_author );
				if ( $user ) {
					$activity = bp_activity_add(
						[
							'action'        => $action,
							'content'       => $action_content,
							'primary_link'  => $action_link,
							'component'     => 'activity',
							'type'          => 'activity_update',
							'user_id'       => $user->ID,
							'hide_sitewide' => true,
						]
					);
			
					if ( is_wp_error( $activity ) ) {
						throw new Exception( $activity->get_error_message() );
					} elseif ( ! $activity ) {
						throw new Exception( 'There is an error on posting stream.' );
					} else {
						if ( class_exists( 'BP_Activity_Activity' ) ) {
							$context = new BP_Activity_Activity( $activity );
							if ( is_object( $context ) ) {
								$context = get_object_vars( $context );
							}
							return array_merge(
								WordPress::get_user_context( $user_id ),
								$context
							);
						}
					}
				} else {
					throw new Exception( 'Author with the email provided not found.' ); 
				}
			} else {
				throw new Exception( 'Please enter valid email address.' );
			}
		}
	}
}

AddPostUserActivityStream::get_instance();
