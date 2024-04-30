<?php
/**
 * QuoteAcceptedJetpackCRM.
 * php version 5.6
 *
 * @category QuoteAcceptedJetpackCRM
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'QuoteAcceptedJetpackCRM' ) ) :

	/**
	 * QuoteAcceptedJetpackCRM
	 *
	 * @category QuoteAcceptedJetpackCRM
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class QuoteAcceptedJetpackCRM {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'JetpackCRM';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'quote_accepted_jetpack_crm';

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
				'label'         => __( 'Quote Accepted', 'suretriggers' ),
				'action'        => 'quote_accepted_jetpack_crm',
				'common_action' => 'jpcrm_quote_accepted',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int|string $quote_id quote ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $quote_id ) {
			if ( empty( $quote_id ) ) {
				return;
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => JetpackCRM::get_quote_context( $quote_id ),
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	QuoteAcceptedJetpackCRM::get_instance();

endif;
