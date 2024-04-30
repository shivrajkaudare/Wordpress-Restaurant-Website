<?php
/**
 * NewProfile.
 * php version 5.6
 *
 * @category NewProfile
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;
use SureTriggers\Integrations\Voxel\Voxel;

/**
 * NewProfile
 *
 * @category NewProfile
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class NewProfile extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_create_new_profile';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create New Profile', 'suretriggers' ),
			'action'   => 'voxel_create_new_profile',
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
	 * 
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		// Get the user ID.
		$user_id = $selected_options['wp_user_id'];

		if ( is_email( $user_id ) ) {
			$user    = get_user_by( 'email', $user_id );
			$user_id = $user ? $user->ID : 1;
		}

		// Check if user exists.
		if ( ! get_userdata( $user_id ) ) {
			throw new Exception( 'User not found' );
		}

		if ( ! class_exists( 'Voxel\User' ) ) {
			return false;
		}

		// Get user details.
		$user = \Voxel\User::get( $user_id );
		// Check if profile is already exists.
		$profile_id = $user->get_profile_id();

		// Create the profile, if not exist.
		if ( ! $profile_id ) {
			$profile    = $user->get_or_create_profile();
			$profile_id = $profile->get_id();
		}

		// Update voxel fields.
		Voxel::voxel_update_post( $fields, $profile_id, 'profile' );

		return [
			'success'        => true,
			'message'        => esc_attr__( 'Profile created successfully', 'suretriggers' ),
			'profile_id'     => $profile_id,
			'profile_url'    => get_author_posts_url( $user_id ),
			'profile_author' => $user_id,
		];
	}

}

NewProfile::get_instance();
