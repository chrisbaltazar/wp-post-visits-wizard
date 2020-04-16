<?php
/**
 * Plugin Name: WP Post Visits Wizard
 * Plugin URI: https://github.com/chrisbaltazar/wp-post-visits-wizard
 * Description: Light plugin to manage visits count for any post and order them as well while listing
 * Version: 1.0.1
 * Author: Chris Baltazar
 **/

use PostVisitsWizard\Bootstrap;

define( 'POST_VISITS_WIZARD_DIR', plugin_dir_path( __FILE__ ) );
define( 'POST_VISITS_WIZARD_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( POST_VISITS_WIZARD_DIR . 'vendor/autoload.php' ) ) {
	require_once POST_VISITS_WIZARD_DIR . 'vendor/autoload.php';
}

Bootstrap::init();