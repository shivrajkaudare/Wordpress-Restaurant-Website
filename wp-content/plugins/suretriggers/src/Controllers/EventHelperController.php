<?php
/**
 * EventHelperController.
 * php version 5.6
 *
 * @category EventHelperController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;

/**
 * EventHelperController
 *
 * @category EventHelperController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class EventHelperController {

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_shortcode( 'sure_trigger_link', [ $this, 'sure_trigger_link' ] );
		add_action( 'init', [ $this, 'sure_trigger_link_click_action' ] );
		add_shortcode( 'sure_trigger_btn', [ $this, 'sure_trigger_btn' ] );
		add_action( 'init', [ $this, 'sure_trigger_btn_click_action' ] );
	}

	/**
	 * Return the HTML template that is displayed by the shortcode
	 *
	 * @param array  $atts The attributes passed in the the shortcode.
	 * @param string $content The content contained by the shortcode.
	 *
	 * @return string|void|bool
	 */
	public static function sure_trigger_btn( $atts, $content = null ) {
		$atts = shortcode_atts(
			[
				'id'    => 0,
				'label' => __( 'Click here', 'suretriggers' ),
			],
			$atts,
			'sure_trigger_btn'
		);

		if ( empty( $atts['id'] ) || 0 === intval( $atts['id'] ) ) {
			return;
		}

		global $post;
		$button_args = '';
		if ( ! empty( $post ) && isset( $post->ID ) && isset( $post->post_title ) ) {
			$button_args  = '<input type="hidden" name="sure_trigger_button_post_id" value="' . $post->ID . '" />';
			$button_args .= '<input type="hidden" name="sure_trigger_button_post_title" value="' . $post->post_title . '" />';
		}

		ob_start();
		?>
		<form method="post" class="sure_trigger_button_form" id="sure_trigger_button_form_<?php echo esc_attr( $atts['id'] ); ?>">
			<input type="hidden" name="sure_trigger_id" value="<?php echo esc_attr( $atts['id'] ); ?>"/><?php echo esc_html( $button_args ); ?>
			<input type="hidden" name="sure_trigger_nonce" value="<?php echo esc_attr( wp_create_nonce( 'sure_trigger_btn_nonce' ) ); ?>"/>
			<button type="submit" class="sure_trigger_button"><?php echo esc_html( $atts['label'] ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle button clicked action.
	 *
	 * @return void
	 */
	public static function sure_trigger_btn_click_action() {
		if ( ! isset( $_POST['sure_trigger_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sure_trigger_nonce'] ) ), 'sure_trigger_btn_nonce' ) ) {
			return;
		}

		$user_id           = ap_get_current_user_id();
		$ap_btn_trigger_id = isset( $_POST['sure_trigger_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sure_trigger_id'] ) ) : '';

		do_action( 'sure_trigger_user_clicks_btn', $ap_btn_trigger_id, $user_id );
	}

	/**
	 * Return the HTML template that is displayed by the shortcode
	 *
	 * @param array  $atts The attributes passed in the the shortcode.
	 * @param string $content The content contained by the shortcode.
	 *
	 * @return string|void
	 */
	public static function sure_trigger_link( $atts, $content = null ) {
		$atts = shortcode_atts(
			[
				'id'   => 0,
				'text' => __( 'Click here', 'suretriggers' ),
			],
			$atts,
			'sure_trigger_link'
		);

		if ( empty( $atts['id'] ) || 0 === intval( $atts['id'] ) ) {
			return;
		}

		$query_args = [];
		global $post;
		if ( ! empty( $post ) && isset( $post->ID ) && isset( $post->post_title ) ) {
			$query_args['ap_link_post_id'] = $post->ID;
		}
		$query_args['ap_link_trigger_id'] = $atts['id'];
		$query_args['ap_link_nonce']      = wp_create_nonce( 'sure_trigger_link_nonce' );
		$link                             = add_query_arg( $query_args );

		return '<a class="sure_trigger_link" href="' . $link . '">' . $atts['text'] . '</a>';
	}

	/**
	 * Handle link clicked action.
	 *
	 * @return void
	 */
	public static function sure_trigger_link_click_action() {
		$nonce = isset( $_GET['ap_link_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_link_nonce'] ) ) : '';

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sure_trigger_link_nonce' ) ) {
			return;
		}

		$user_id = ap_get_current_user_id();

		$ap_link_trigger_id = absint( isset( $_GET['ap_link_trigger_id'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_link_trigger_id'] ) ) : '' );

		do_action( 'sure_trigger_user_clicks_link', $ap_link_trigger_id, $user_id );
		$refresh = remove_query_arg( [ 'ap_link_trigger_id', 'ap_link_nonce', 'ap_link_post_id' ] );

		wp_safe_redirect( $refresh );
		exit();
	}

	/**
	 * Sure Triggers connection URL.
	 *
	 * @return string
	 */
	public static function get_sure_triggers_url() {
		return defined( 'SURE_TRIGGERS_URL' ) ? SURE_TRIGGERS_URL : apply_filters( 'sure_triggers_domain', 'https://staging-sc.bsf.io/' );
	}

}

/**
 * Ignore false positive
 *
 * @psalm-suppress UndefinedMethod
 */
EventHelperController::get_instance();
