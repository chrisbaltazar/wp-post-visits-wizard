<?php

namespace PostVisitsWizard;


/**
 * Class Bootstrap
 * @package SimpleNotify
 */
class Bootstrap {

	const PLUGIN_NAME = 'wp-post-visits-wizard';

	const MENU_SLUG = 'post-visits-wizard-admin';

	/**
	 * @var Settings
	 */
	private $settings;
	/**
	 * @var Controller
	 */
	private $controller;

	/**
	 * Bootstrap constructor.
	 *
	 * @param Settings $settings
	 * @param Controller $controller
	 */
	public function __construct( Settings $settings, Controller $controller ) {
		$this->settings   = $settings;
		$this->controller = $controller;
		$this->controller->run();
	}

	/**
	 * Starts the magic
	 */
	public static function init() {
		$settings = Settings::init();
		$obj      = new self( $settings, new Controller( $settings ) );

		add_action( 'admin_enqueue_scripts', [ $obj, 'handle_assets' ] );

		add_action( 'admin_menu', [ $obj, 'set_admin_menu' ] );

		add_action( 'wp-post-visits-wizard-app', [ $obj, 'set_main_app' ] );
	}


	/**
	 *
	 */
	public function set_main_app() {
		wp_enqueue_script( 'main-app', POST_VISITS_WIZARD_URL . '/src/js/main.js', [ 'vue-resource' ] );
		wp_localize_script( 'main-app', 'pvwData', $this->settings->get_data() ?: (object) [] );
		wp_localize_script( 'main-app', 'pvwEndpoint', $this->settings->get_endpoints() ?: (object) [] );
	}

	/**
	 *
	 */
	public function handle_assets() {
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		wp_enqueue_style( 'boostrap-css', POST_VISITS_WIZARD_URL . '/src/assets/bootstrap-4.4.1.min.css' );
		wp_enqueue_script( 'bootstrap-js', POST_VISITS_WIZARD_URL . '/src/assets/bootstrap-4.4.1.min.js' );
		wp_enqueue_script( 'font-awesome', POST_VISITS_WIZARD_URL . '/src/assets/font-awesome-4.7.0.min.css' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_enqueue_script( 'vue-js', POST_VISITS_WIZARD_URL . '/src/assets/vue-dev.js' );
		} else {
			wp_enqueue_script( 'vue-js', POST_VISITS_WIZARD_URL . '/src/assets/vue@2.6.11.js' );
		}

		wp_enqueue_script( 'vue-resource', POST_VISITS_WIZARD_URL . '/src/assets/vue-resource@1.5.1.js', [ 'vue-js' ] );
	}

	/**
	 *
	 */
	public function set_admin_menu() {
		add_submenu_page(
			'options-general.php',
			'WP Post Visits Wizard',
			'WP Post Visits Wizard',
			'manage_options',
			self::MENU_SLUG,
			function () {
				include __DIR__ . '/templates/main-settings.php';
			}
		);
	}

	/**
	 * @return bool
	 */
	public function is_plugin_page(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		return strpos( $screen->id, self::MENU_SLUG ) !== false;
	}
}