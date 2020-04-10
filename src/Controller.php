<?php

namespace PostVisitsWizard;

/**
 * Class Controller
 * @package SimpleNotify
 */
class Controller {
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * Controller constructor.
	 *
	 * @param Settings $settings
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Put everything to work now
	 */
	public function run() {
		add_action( 'wp', [ $this, 'register_visit' ] );
	}


	public function register_visit() {
		if ( ! is_single() || is_admin() ) {
			return;
		}


	}
}