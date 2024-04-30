<?php
/**
 * AddTagToContact.
 * php version 5.6
 *
 * @category AddTagToContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailMint\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Mint\MRM\DataBase\Models\ContactModel;
use Mint\MRM\DataBase\Models\ContactGroupModel;
use Exception;

/**
 * AddTagToContact
 *
 * @category AddTagToContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddTagToContact extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MailMint';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mail_mint_add_tags_to_contact';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Tag To Contact', 'suretriggers' ),
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
	 *
	 * @return array|void
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) || ! class_exists( 'Mint\MRM\DataBase\Models\ContactModel' ) ) {
			return;
		}
		$contact_id    = $selected_options['contact_id'] ? $selected_options['contact_id'] : 0;
		$selected_tags = $selected_options['contact_tags'] ? explode( ',', $selected_options['contact_tags'] ) : [];
		if ( ! ContactModel::is_contact_ids_exists( [ $contact_id ] ) ) {
			throw new Exception( 'There is no contact with provided id.' );
		}
		$tags_data = [];
		foreach ( $selected_tags as $tag ) {
			$tags        = ContactGroupModel::get_or_insert_contact_group_by_title( $tag, 'tags' );
			$tags_data[] = $tags;
		}
		$contact_details = ContactGroupModel::set_tags_to_contact( $tags_data, $contact_id );
		
		if ( $contact_details ) {
			$contact_details = ContactModel::get( $contact_id );
			$contact_tags    = ContactGroupModel::get_tags_to_contact( [ 'id' => $contact_id ] );
			foreach ( $contact_tags['tags'] as $tag ) {
				$contact_details['tags'][] = [
					'id'    => $tag->id,
					'title' => $tag->title,
				];
			}       
		}
		return $contact_details;
	}
	
	

}

AddTagToContact::get_instance();
