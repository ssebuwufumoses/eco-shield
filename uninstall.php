<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is strictly for cleaning up. It deletes the
 * 'eco-shield-thumbs' folder and cleans up database options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// 1. Define the cache directory with PREFIXED variables
$wpes_uninstall_upload = wp_get_upload_dir();
$wpes_uninstall_cache  = $wpes_uninstall_upload['basedir'] . '/eco-shield-thumbs';

// 2. Helper function to recursively delete a folder
function wpes_recursive_remove_dir( $dir ) {
    if ( is_dir( $dir ) ) {
        $objects = scandir( $dir );
        foreach ( $objects as $object ) {
            if ( $object != "." && $object != ".." ) {
                if ( is_dir( $dir . DIRECTORY_SEPARATOR . $object ) && ! is_link( $dir . "/" . $object ) ) {
                    wpes_recursive_remove_dir( $dir . DIRECTORY_SEPARATOR . $object );
                } else {
                    wp_delete_file( $dir . DIRECTORY_SEPARATOR . $object );
                }
            }
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
        rmdir( $dir );
    }
}

// 3. Execute File Cleanup
if ( is_dir( $wpes_uninstall_cache ) ) {
    wpes_recursive_remove_dir( $wpes_uninstall_cache );
}

// 4. Execute Database Cleanup (Fixed: Added total_plays)
delete_option( 'wpes_options' );       // Deletes the settings
delete_option( 'wpes_carbon_saved' );  // Deletes the carbon stats
delete_option( 'wpes_total_plays' );   // Deletes the play counter