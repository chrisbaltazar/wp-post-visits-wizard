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
		$this->settings = $settings;
	}

	/**
	 * Put everything to work now
	 */
	public function run() {
		add_action( 'wp', [ $this, 'register_visit' ], 10 );
		add_filter( 'the_posts', [ $this, 'set_custom_order' ], 10, 2 );
		add_action( 'wp_loaded', [ $this, 'handle_post_tables' ], 10 );
	}

	/**
	 * Set filter to manage admin tables
	 */
	public function handle_post_tables() {
		$settings = $this->get_settings();
		foreach ( $settings['types'] as $cpt ) {
			$filter_column = sprintf( 'manage_%s_posts_columns', $cpt );
			$filter_data   = sprintf( 'manage_%s_posts_custom_column', $cpt );

			if ( $cpt === 'post' ) {
				$filter_column = str_replace( '_post_', '_', $filter_column );
				$filter_data   = str_replace( '_post_', '_', $filter_data );
			}

			add_filter( $filter_column, [ $this, 'add_post_table_column' ], 1, 1 );
			add_action( $filter_data, [ $this, 'set_post_column_data' ], 10, 2 );
		}
	}

	/**
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_post_table_column( $columns ) {
		if ( in_array( self::POST_TABLE_COLUMN, $columns ) ) {
			return $columns;
		}

		$columns[] = self::POST_TABLE_COLUMN;

		return $columns;
	}

	/**
	 * @param $column
	 * @param $post_id
	 */
	public function set_post_column_data( $column, $post_id ) {
		if ( is_string( $column ) && $column !== self::POST_TABLE_COLUMN ) {
			return;
		}

		$count = get_post_meta( $post_id, self::META_COUNTER, true ) ?: 0;

		echo '<div style = "text-align:center">' . $count . '</div>';
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
	 * @param $posts
	 * @param $query
	 *
	 * @return void|\WP_Query
	 */
	public function set_custom_order( $posts, $query ) {
		if ( is_admin() || ! $this->should_order( $query ) ) {
			return $posts;
		}

		usort( $posts, function ( $post1, $post2 ) {
			$c1 = get_post_meta( $post1->ID, self::META_COUNTER, true ) ?: 0;
			$c2 = get_post_meta( $post2->ID, self::META_COUNTER, true ) ?: 0;

			return $c2 <=> $c1;
		} );

		return $posts;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function should_update( \WP_Post $post ): bool {
		$settings = $this->get_settings();

		if ( ! in_array( $post->post_type, $settings['types'] ) || $post->post_status !== 'publish' ) {
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

	private function should_order( \WP_Query $query ): bool {
		$settings        = $this->get_settings();
		$query_post_type = $query->query['post_type'] ?? $query->query_vars['post_type'] ?? '';
		if ( in_array( $query_post_type, $settings['types'] ) ) {
			return true;
		}

		return empty( $query_post_type ) && ( $query->is_archive() || $query->is_category() || $query->is_tag() );
	}

	/**
	 * @return array
	 */
	private function get_settings(): array {
		$settings_data = $this->settings->get_data();

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