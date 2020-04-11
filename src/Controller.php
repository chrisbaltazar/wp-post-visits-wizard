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

		$post = get_post();

		if ( ! $this->should_update( $post ) ) {
			return;
		}

		$counter = get_post_meta( $post->ID, self::META_COUNTER, true ) ?: 0;

		update_post_meta( $post->ID, self::META_COUNTER, ++ $counter );
	}

	/**
	 * @param $query
	 */
	public function set_custom_order( \WP_Query $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$query->query_vars['order']      = 'DESC';
		$query->query_vars['orderby']    = 'meta_value menu_order date';
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

	/**
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function should_update( \WP_Post $post ): bool {
		$settings = $this->get_settings();

		if ( ! in_array( $post->post_type, $settings['types'] ) ) {
			return false;
		}

		$post_categories = wp_get_post_categories( $post->ID, [ 'fields' => 'slugs' ] );
		if ( ! empty( $settings['categories'] ) && is_array( $post_categories ) ) {
			$finder = array_intersect( $settings['categories'], $post_categories );
			if ( empty( $finder ) ) {
				return false;
			}
		}

		$post_tags = wp_get_post_tags( $post->ID, [ 'fields' => 'slugs' ] );
		if ( ! empty( $settings['tags'] ) && is_array( $post_tags ) ) {
			$finder = array_intersect( $settings['tags'], $post_tags );
			if ( empty( $finder ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array
	 */
	private function get_settings(): array {
		$settings = $this->settings->get_data();

		$settings['types'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings['types'], function ( $type ) {
			return $type['active'];
		} ) );

		$settings['categories'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings['categories'], function ( $cat ) {
			return $cat['active'];
		} ) );

		$settings['tags'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings['tags'], function ( $tag ) {
			return $tag['active'];
		} ) );

		return $settings;
	}
}