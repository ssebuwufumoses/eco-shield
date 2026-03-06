<?php
/**
 * Plugin Name:       Eco-Shield
 * Plugin URI:        https://wordpress.org/plugins/eco-shield/
 * Description:       Boost PageSpeed and reduce carbon footprint by replacing YouTube & Vimeo embeds with a smart, privacy-focused static facade.
 * Version:           1.2.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            ssebuwufumoses
 * Author URI:        https://profiles.wordpress.org/ssebuwufumoses/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eco-shield
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define constants
 */
define( 'WPES_VERSION', '1.2.1' );
define( 'WPES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPES_URL', plugin_dir_url( __FILE__ ) );

/**
 * The "Heartbeat": Load Core + Settings
 */
function wpes_run_shield() {
    require_once WPES_PATH . 'inc/class-shield-core.php';
    
    // Load Settings Page Logic (Only in Admin)
    if ( is_admin() ) {
        require_once WPES_PATH . 'inc/class-shield-settings.php';
    }
}
add_action( 'plugins_loaded', 'wpes_run_shield' );

/**
 * Enqueue Assets
 */
function wpes_enqueue_assets() {
    if ( ! is_admin() ) {
        wp_enqueue_style(
            'wpes-style', 
            WPES_URL . 'assets/css/shield-style.css', 
            array(), 
            WPES_VERSION
        );

        // --- Feature 1: User Branding ---
        $options = get_option( 'wpes_options' );
        $color   = isset( $options['brand_color'] ) ? $options['brand_color'] : '#ff0000';
        $color   = sanitize_hex_color($color) ? $color : '#ff0000';
        
        $custom_css = ":root { --wpes-brand-color: {$color}; }";
        wp_add_inline_style( 'wpes-style', $custom_css );
        // --------------------------------

        wp_enqueue_script(
            'wpes-lazy-load', 
            WPES_URL . 'assets/js/shield-lazy-load.js', 
            array(), 
            WPES_VERSION, 
            true 
        );

        // --- NEW Feature 4: Pass Data to JS for Analytics ---
        wp_localize_script( 'wpes-lazy-load', 'wpes_vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpes_track_play' )
        ]);
        // ----------------------------------------------------
    }
}
add_action( 'wp_enqueue_scripts', 'wpes_enqueue_assets' );