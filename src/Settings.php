<?php


namespace PostVisitsWizard;


/**
 * Class Settings
 * @package SimpleNotify
 */
class Settings {

	const ENDPOINT_SAVE_CONFIG = '/save';

	const OPTION_CONFIG_NAME = Bootstrap::PLUGIN_NAME . '-config';

	/**
	 * @var array
	 */
	private $stored_data;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->stored_data = get_option( self::OPTION_CONFIG_NAME, [] );
	}

	/**
	 * @return Settings
	 */
	public static function init() {
		$obj = new self();

		add_action( 'rest_api_init', [ $obj, 'register_rest_route' ] );

		return $obj;
	}

	/**
	 *
	 */
	public function register_rest_route() {
		register_rest_route( Bootstrap::PLUGIN_NAME, self::ENDPOINT_SAVE_CONFIG,
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'save' ],
			] );
	}

	/**
	 * @return array
	 */
	public function get_endpoints(): array {
		return [
			'save' => '/wp-json/' . trim( Bootstrap::PLUGIN_NAME, '\\/' ) . '/' . ltrim( self::ENDPOINT_SAVE_CONFIG, '/' ),
		];
	}

	/**
	 * @return array
	 */
	public function get_data(): array {
		return [
			'types'      => $this->get_post_types(),
			'categories' => $this->get_categories(),
			'tags'       => $this->get_tags(),
		];
	}

	private function get_post_types(): array {
		$args = [
			'show_ui'      => true,
			'show_in_menu' => true,
			'_builtin'     => false,
		];

		$custom_post_types = get_post_types( $args, 'objects', 'and' );

		foreach ( $custom_post_types as $key => $cpt ) {
			$custom_post_types[] = [ 'id' => $key, 'name' => $cpt->label ];
		}

		$default = [ [ 'id' => 'post', 'name' => __( 'Posts' ) ] ];

		return array_merge( $default, $custom_post_types );
	}

	private function get_categories(): array {
		$categories = [];

		foreach ( get_categories( [ 'hide_empty' => false ] ) as $category ) {
			$categories[] = [ 'id' => $category->slug, 'name' => $category->name ];
		}

		return $categories;
	}

	private function get_tags(): array {
		$tags = [];

		foreach ( get_categories( [ 'hide_empty' => false ] ) as $tag ) {
			$tags[] = [ 'id' => $tag->slug, 'name' => $tag->name ];
		}

		return $tags;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function save( \WP_REST_Request $request ) {
		$request_data = $this->get_request_data( $request->get_body_params() );

		if ( empty( $request_data ) ) {
			return new \WP_REST_Response( 'Action not allowed', 500 );
		}

		$persist_data = $this->get_persist_data( $request_data );

		update_option( self::OPTION_CONFIG_NAME, $persist_data );

		return new \WP_REST_Response( 'Option successfully saved!' );
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_request_data( array $params ): array {
		$target = $params['target'] ?? '';
		$id     = sanitize_text_field( $params['id'] ?? '' );
		$stat   = $params['stat'] ?? '';

		if ( ! in_array( $target, [ 'types', 'categories', 'tags' ] ) ) {
			return [];
		}

		return [
			'target' => $target,
			'id'     => $id,
			'stat'   => (bool) $stat
		];
	}

	/**
	 * @param array $request
	 *
	 * @return array
	 */
	private function get_persist_data( array $request ): array {
		if ( ! $request['stat'] ) {
			$this->stored_data[ $request['target'] ][ $request['id'] ] = true;

			return $this->stored_data;
		}

		unset( $this->stored_data[ $request['target'] ][ $request['id'] ] );

		return $this->stored_data;
	}

}