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

	/**
	 * @return array
	 */
	private function get_post_types(): array {
		$args = [
			'show_ui'      => true,
			'show_in_menu' => true,
			'_builtin'     => false,
		];

		$cpts              = get_post_types( $args, 'objects', 'and' );
		$custom_post_types = [];

		foreach ( $cpts as $key => $cpt ) {
			$custom_post_types[] = [
				'id'     => $key,
				'name'   => $cpt->label,
				'active' => in_array( $key, $this->stored_data['types'] ?? [] ) ? 1 : 0
			];
		}

		$default = [
			[
				'id'     => 'post',
				'name'   => __( 'Posts' ),
				'active' => in_array( 'post', $this->stored_data['types'] ?? [] ) ? 1 : 0
			]
		];

		return array_merge( $default, $custom_post_types );
	}

	/**
	 * @return array
	 */
	private function get_categories(): array {
		$categories = [];

		foreach ( get_categories( [ 'hide_empty' => false ] ) as $category ) {
			$categories[] = [
				'id'     => $category->slug,
				'name'   => $category->name,
				'active' => in_array( $category->slug, $this->stored_data['categories'] ?? [] ) ? 1 : 0
			];
		}

		return $categories;
	}

	/**
	 * @return array
	 */
	private function get_tags(): array {
		$tags = [];

		foreach ( get_tags( [ 'hide_empty' => false ] ) as $tag ) {
			$tags[] = [
				'id'     => $tag->slug,
				'name'   => $tag->name,
				'active' => in_array( $tag->slug, $this->stored_data['tags'] ?? [] ) ? 1 : 0
			];
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

		$this->persist_data( $request_data );

		update_option( self::OPTION_CONFIG_NAME, $this->stored_data );

		return new \WP_REST_Response( $this->get_data() );
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_request_data( array $params ): array {
		$target = $params['target'] ?? '';
		$id     = sanitize_text_field( $params['id'] ?? '' );

		if ( ! in_array( $target, [ 'types', 'categories', 'tags' ] ) ) {
			return [];
		}

		return [
			'target' => $target,
			'id'     => $id,
		];
	}

	/**
	 * @param array $request
	 *
	 * @return array
	 */
	private function persist_data( array $request ): array {
		if ( ! $this->exist( $request ) ) {
			$this->insert( $request );

			return $this->stored_data;
		}

		$this->delete( $request );

		return $this->stored_data;
	}

	/**
	 * @param $payload
	 *
	 * @return bool
	 */
	private function exist( $payload ): bool {
		return in_array( $payload['id'], $this->stored_data[ $payload['target'] ] ?? [] );
	}

	/**
	 * @param array $request
	 */
	private function delete( array $request ) {
		$finder = array_search( $request['id'], $this->stored_data[ $request['target'] ] );
		unset( $this->stored_data[ $request['target'] ][ $finder ] );
	}

	/**
	 * @param array $request
	 */
	private function insert( array $request ) {
		$this->stored_data[ $request['target'] ][] = $request['id'];
	}
}