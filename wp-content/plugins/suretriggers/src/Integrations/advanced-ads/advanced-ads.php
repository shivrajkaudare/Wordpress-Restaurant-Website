<?php
/**
 * AdvancedAds core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\AdvancedAds;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\AdvancedAds
 */
class AdvancedAds extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'AdvancedAds';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Advanced Ads', 'suretriggers' );
		$this->description = __( 'A Powerful WordPress Ad Management Plugin. Advanced Ads is a great plugin that makes it easier to manage your ads.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/advanced-ads.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'Advanced_Ads' );
	}
}

IntegrationsController::register( AdvancedAds::class );
