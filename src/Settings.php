<?php


namespace PostVisitsWizard;


/**
 * Class Settings
 * @package SimpleNotify
 */
class Settings {

	const ENDPOINT_SAVE_CONFIG = '/save';

	const ENDPOINT_SET_ACTION = '/action';

	const ENDPOINT_TEST_EMAIL = '/test';

	const OPTION_CONFIG_NAME = Bootstrap::PLUGIN_NAME . '-config';

	const OPTION_ACTION_NAME = Bootstrap::PLUGIN_NAME . '-actions';

	const DEFINED_PWD_VAR = 'DEFINED_PWD';

	/**
	 *
	 */
	const PLUGIN_ACTIONS = [
		'comment_for_author' => 'Notify new comments to post author',
		'comment_for_user'   => 'Notify new replies to visitor\'s comments',
	];

	/**
	 * @var array
	 */
	private $stored_data;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->stored_data = [
			'config'  => get_option( self::OPTION_CONFIG_NAME, [] ),
			'actions' => get_option( self::OPTION_ACTION_NAME, [] ),
		];
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

		register_rest_route( Bootstrap::PLUGIN_NAME, self::ENDPOINT_SET_ACTION,
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'switch' ],
			] );

		register_rest_route( Bootstrap::PLUGIN_NAME, self::ENDPOINT_TEST_EMAIL,
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'test_email' ],
			] );
	}

	/**
	 * @return array
	 */
	public function get_endpoints(): array {
		return [
			'save'   => '/wp-json/' . trim( Bootstrap::PLUGIN_NAME, '\\/' ) . '/' . ltrim( self::ENDPOINT_SAVE_CONFIG, '/' ),
			'action' => '/wp-json/' . trim( Bootstrap::PLUGIN_NAME, '\\/' ) . '/' . ltrim( self::ENDPOINT_SET_ACTION, '/' ),
			'test'   => '/wp-json/' . trim( Bootstrap::PLUGIN_NAME, '\\/' ) . '/' . ltrim( self::ENDPOINT_TEST_EMAIL, '/' )
		];
	}

	/**
	 * @return array
	 */
	public function get_data(): array {
		return [
			'cpt'        => $this->get_post_types(),
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
			$custom_post_types[ $key ] = [ 'name' => $cpt->label ];
		}

		$default = [
			'post' => [ 'name' => __( 'Posts' ) ],
			'page' => [ 'name' => __( 'Pages' ) ],
		];

		return array_merge( $default, $custom_post_types );
	}

	private function get_categories(): array {
		$categories = [];

		foreach ( get_categories( [ 'hide_empty' => false ] ) as $category ) {
			$categories[ $category->slug ] = [ 'name' => $category->name ];
		}

		return $categories;
	}

	private function get_tags(): array {
		$tags = [];

		foreach ( get_categories( [ 'hide_empty' => false ] ) as $tag ) {
			$tags[ $tag->slug ] = [ 'name' => $tag->name ];
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
			return new \WP_REST_Response( 'Please check again your entries and try again.', 500 );
		}

		update_option( self::OPTION_CONFIG_NAME, $request_data );

		return new \WP_REST_Response( 'Settings successfully saved!' );
	}

}