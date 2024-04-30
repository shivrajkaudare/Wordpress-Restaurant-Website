<?php
/**
 * SendEmailToListContacts.
 * php version 5.6
 *
 * @category SendEmailToListContacts
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use DateTime;
use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * SendEmailToListContacts
 *
 * @category SendEmailToListContacts
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendEmailToListContacts extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'fluentcrm_send_email_to_list_contacts';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send Email', 'suretriggers' ),
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
	 * @return array|void|mixed
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$username = $selected_options['api_username'];
		$password = $selected_options['api_password'];

		if ( ! is_array( $selected_options['campaign'] ) ) {
			$args = [
				'headers'   => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
				],
				'sslverify' => false,
				'body'      => wp_json_encode( [ 'title' => $selected_options['campaign'] ] ),
			];
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$request = wp_remote_post( $selected_options['wordpress_url'] . '/wp-json/fluent-crm/v2/campaigns', $args );
		} else {
			$args    = [
				'headers'   => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
				],
				'sslverify' => false,
			];
			$request = wp_remote_get( $selected_options['wordpress_url'] . '/wp-json/fluent-crm/v2/campaigns/' . $selected_options['campaign']['value'], $args );
		}
		$response_code    = wp_remote_retrieve_response_code( $request );
		$response_body    = wp_remote_retrieve_body( $request );
		$response_context = json_decode( $response_body, true );
		if ( 200 !== $response_code ) {
			return $response_context;
		}
		$args = array_merge(
			$args,
			[
				'method' => 'PUT',
			]
		);
		if ( is_array( $response_context ) ) {
			$args['body'] = wp_json_encode(
				[
					'title'         => $response_context['title'],
					'email_body'    => $selected_options['email_body'],
					'email_subject' => $selected_options['email_subject'],
					'settings'      => [
						'mailer_settings'     => [
							'from_name'      => $selected_options['from_name'],
							'is_custom'      => 'yes',
							'from_email'     => $selected_options['from_email'],
							'reply_to_name'  => '',
							'reply_to_email' => '',
						],
						'subscribers'         => [
							[
								'list' => $selected_options['list_id'],
								'tag'  => $selected_options['tag_id'],
							],
						],
						'excludedSubscribers' => null,
						'sending_filter'      => 'list_tag',
						'dynamic_segment'     => [
							'id'   => '',
							'slug' => '',
						],
						'advanced_filters'    => [
							[],
						],
					],
				]
			);
		}
		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		$settings_request       = wp_remote_request( $selected_options['wordpress_url'] . '/wp-json/fluent-crm/v2/campaigns/' . $response_context['id'], $args );
		$settings_response_code = wp_remote_retrieve_response_code( $settings_request );
		$settings_response_body = wp_remote_retrieve_body( $settings_request );
		$settings_context       = json_decode( $settings_response_body, true );
		if ( 200 !== $settings_response_code ) {
			return $settings_context;
		}
		if ( ! empty( $args['body'] ) ) {
			$args = [
				'headers'   => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
				],
				'sslverify' => false,
				'body'      => $args['body'],
			];
		}
		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		$final_request       = wp_remote_post( $selected_options['wordpress_url'] . '/wp-json/fluent-crm/v2/campaigns/' . $settings_context['campaign']['id'] . '/schedule', $args );
		$final_response_body = wp_remote_retrieve_body( $final_request );
		$final_context       = json_decode( $final_response_body, true );
		return $final_context;
	}

}

SendEmailToListContacts::get_instance();
