<?php

namespace PostVisitsWizard;

/**
 * Class Controller
 * @package SimpleNotify
 */
class Controller {

	/**
	 *
	 */
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
		add_action( 'wp', [ $this, 'register_visit' ], 10 );
		add_action( 'pre_get_posts', [ $this, 'set_custom_order' ], 10, 1 );
	}


	/**
	 *
	 */
	public function register_visit() {
		if ( ! is_single() || is_admin() ) {
			return;
		}

		$post_id = get_the_ID();

		$counter = get_post_meta( $post_id, self::META_COUNTER, true ) ?: 0;

		update_post_meta( $post_id, self::META_COUNTER, ++ $counter );
	}

	/**
	 * @param $query
	 */
	public function set_custom_order( \WP_Query $query ) {
		if ( ( ! is_category() || ! is_archive() ) || ! $query->is_main_query() ) {
			return;
		}

		$query->query_vars['order']      = 'DESC';
		$query->query_vars['orderby']    = 'meta_value date';
		$query->query_vars['meta_query'] = [
			'relation' => 'OR',
			[
				'key'     => self::META_COUNTER,
				'compare' => 'EXISTS'
			],
			[
				'key'     => self::META_COUNTER,
				'compare' => 'NOT EXISTS'
			]
		];
	}
}