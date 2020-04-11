<?php

namespace PostVisitsWizard;

/**
 * Class Controller
 * @package SimpleNotify
 */
class Controller {

	const META_COUNTER = '_post_visits_counter';
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

		$post_id = get_the_ID();

		$counter = get_post_meta( $post_id, self::META_COUNTER, true ) ?: 0;

		update_post_meta( $post_id, self::META_COUNTER, ++ $counter );
	}
}