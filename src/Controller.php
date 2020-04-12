<?php

namespace PostVisitsWizard;

/**
 * Class Controller
 * @package SimpleNotify
 */
class Controller {

	const META_COUNTER = '_post_visits_counter';

	const POST_TABLE_COLUMN = 'Total visits';

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
		$this->settings = $this->get_settings( $settings->get_data() );
	}

	/**
	 * Put everything to work now
	 */
	public function run() {
		add_action( 'wp', [ $this, 'register_visit' ], 10 );
		add_action( 'pre_get_posts', [ $this, 'set_custom_order' ], 10, 1 );

		foreach ( $this->settings['types'] as $cpt ) {
			$filter_column = sprintf( 'manage_%s_posts_columns', $cpt );
			$filter_data   = sprintf( 'manage_%s_posts_custom_column', $cpt );

			if ( $cpt === 'post' ) {
				$filter_column = str_replace( '_posts', '', $filter_column );
				$filter_data   = str_replace( '_posts', '', $filter_data );
			}

			add_filter( $filter_column, [ $this, 'add_post_table_column' ], 10, 1 );
			add_action( $filter_data, [ $this, 'set_post_column_data' ], 10, 2 );
		}
	}

	/**
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_post_table_column( $columns ) {
		$columns[] = self::POST_TABLE_COLUMN;

		return $columns;
	}

	/**
	 * @param $column
	 * @param $post_id
	 */
	public function set_post_column_data( $column, $post_id ) {
		if ( $column !== self::POST_TABLE_COLUMN ) {
			return;
		}

		echo get_post_meta( $post_id, self::META_COUNTER, true ) ?: 0;
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
		if ( ! in_array( $post->post_type, $this->settings['types'] ) ) {
			return false;
		}

		$post_categories = wp_get_post_categories( $post->ID, [ 'fields' => 'slugs' ] );
		if ( ! empty( $this->settings['categories'] ) && is_array( $post_categories ) ) {
			$finder = array_intersect( $this->settings['categories'], $post_categories );
			if ( empty( $finder ) ) {
				return false;
			}
		}

		$post_tags = wp_get_post_tags( $post->ID, [ 'fields' => 'slugs' ] );
		if ( ! empty( $this->settings['tags'] ) && is_array( $post_tags ) ) {
			$finder = array_intersect( $this->settings['tags'], $post_tags );
			if ( empty( $finder ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $settings_data
	 *
	 * @return array
	 */
	private function get_settings( array $settings_data ): array {
		$settings_data['types'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings_data['types'], function ( $type ) {
			return $type['active'];
		} ) );

		$settings_data['categories'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings_data['categories'], function ( $cat ) {
			return $cat['active'];
		} ) );

		$settings_data['tags'] = array_map( function ( $item ) {
			return $item['id'];
		}, array_filter( $settings_data['tags'], function ( $tag ) {
			return $tag['active'];
		} ) );

		return $settings_data;
	}
}