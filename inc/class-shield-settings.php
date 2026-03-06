<?php
/**
 * Eco-Shield Settings Logic
 * Handles the "Settings -> Eco-Shield" page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPES_Shield_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        // --- NEW: Reset Hook ---
        add_action( 'admin_init', [ __CLASS__, 'process_reset_stats' ] );
    }

    public static function add_admin_menu() {
        add_options_page(
            esc_html__( 'Eco-Shield Settings', 'eco-shield' ),
            'Eco-Shield',
            'manage_options',
            'eco-shield',
            [ __CLASS__, 'render_settings_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'wpes_options_group', 'wpes_options', [ __CLASS__, 'sanitize' ] );

        // --- SECTION 1: Interface Settings ---
        add_settings_section(
            'wpes_general_section',
            esc_html__( 'Interface Settings', 'eco-shield' ),
            null,
            'eco-shield'
        );

        add_settings_field(
            'enable_lightbox',
            esc_html__( 'Lightbox Mode', 'eco-shield' ),
            [ __CLASS__, 'checkbox_callback' ],
            'eco-shield',
            'wpes_general_section',
            [ 'id' => 'enable_lightbox', 'desc' => esc_html__( 'Play videos in a popup modal overlay instead of inline.', 'eco-shield' ) ]
        );

        add_settings_field(
            'show_widget',
            esc_html__( 'Dashboard Widget', 'eco-shield' ),
            [ __CLASS__, 'checkbox_callback' ],
            'eco-shield',
            'wpes_general_section',
            [ 'id' => 'show_widget', 'desc' => esc_html__( 'Show the "Eco-Shield Impact" stats on the dashboard home.', 'eco-shield' ) ]
        );

        add_settings_field(
            'show_admin_bar',
            esc_html__( 'Admin Toolbar', 'eco-shield' ),
            [ __CLASS__, 'checkbox_callback' ],
            'eco-shield',
            'wpes_general_section',
            [ 'id' => 'show_admin_bar', 'desc' => esc_html__( 'Show the "Purge Eco-Shield" button in the top bar.', 'eco-shield' ) ]
        );

        add_settings_field(
            'show_toast',
            esc_html__( 'Notifications', 'eco-shield' ),
            [ __CLASS__, 'checkbox_callback' ],
            'eco-shield',
            'wpes_general_section',
            [ 'id' => 'show_toast', 'desc' => esc_html__( 'Show the green "Cache Purged" popup message.', 'eco-shield' ) ]
        );

        // --- SECTION 2: Design Settings ---
        add_settings_section(
            'wpes_design_section',
            esc_html__( 'Player Design', 'eco-shield' ),
            null,
            'eco-shield'
        );

        add_settings_field(
            'brand_color',
            esc_html__( 'Play Button Color', 'eco-shield' ),
            [ __CLASS__, 'color_picker_callback' ],
            'eco-shield',
            'wpes_design_section',
            [ 'id' => 'brand_color', 'desc' => esc_html__( 'Choose a color to match your brand (Default: #ff0000).', 'eco-shield' ) ]
        );

        // --- SECTION 3: Privacy Settings ---
        add_settings_section(
            'wpes_privacy_section',
            esc_html__( 'Privacy Compliance', 'eco-shield' ),
            null,
            'eco-shield'
        );

        add_settings_field(
            'enable_privacy_text',
            esc_html__( 'Show Privacy Notice', 'eco-shield' ),
            [ __CLASS__, 'checkbox_callback' ],
            'eco-shield',
            'wpes_privacy_section',
            [ 'id' => 'enable_privacy_text', 'desc' => esc_html__( 'Display a text overlay warning users before they load the video.', 'eco-shield' ) ]
        );

        add_settings_field(
            'privacy_text',
            esc_html__( 'Notice Text', 'eco-shield' ),
            [ __CLASS__, 'text_input_callback' ],
            'eco-shield',
            'wpes_privacy_section',
            [ 
                'id' => 'privacy_text', 
                /* translators: %s: "YouTube" or "Vimeo" */
                'default' => 'By clicking, you agree to load content from %s.', 
                /* translators: %s: "YouTube" or "Vimeo" */
                'desc' => esc_html__( 'Use %s to automatically insert "YouTube" or "Vimeo".', 'eco-shield' ) 
            ]
        );
    }

    // --- Callbacks ---

    public static function checkbox_callback( $args ) {
        $options = get_option( 'wpes_options' );
        $id      = $args['id'];
        $value   = isset( $options[ $id ] ) ? $options[ $id ] : 0;
        ?>
        <label>
            <input type="checkbox" name="wpes_options[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( $value, 1 ); ?> />
            <?php echo esc_html( $args['desc'] ); ?>
        </label>
        <?php
    }

    public static function color_picker_callback( $args ) {
        $options = get_option( 'wpes_options' );
        $value = isset( $options['brand_color'] ) ? $options['brand_color'] : '#ff0000';
        ?>
        <div style="display:flex; align-items:center; gap:10px;">
            <input type="color" name="wpes_options[brand_color]" value="<?php echo esc_attr( $value ); ?>" style="height:30px; width:50px; border:none; padding:0; cursor:pointer;">
            <span style="color:#646970; font-style:italic;"><?php echo esc_html( $args['desc'] ); ?></span>
        </div>
        <?php
    }

    public static function text_input_callback( $args ) {
        $options = get_option( 'wpes_options' );
        $id      = $args['id'];
        $default = isset( $args['default'] ) ? $args['default'] : '';
        $value   = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        ?>
        <input type="text" name="wpes_options[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description"><?php echo esc_html( $args['desc'] ); ?></p>
        <?php
    }

    public static function sanitize( $input ) {
        $new_input = [];
        
        $new_input['enable_lightbox'] = isset( $input['enable_lightbox'] ) ? 1 : 0; 
        $new_input['show_widget']    = isset( $input['show_widget'] ) ? 1 : 0;
        $new_input['show_admin_bar'] = isset( $input['show_admin_bar'] ) ? 1 : 0;
        $new_input['show_toast']     = isset( $input['show_toast'] ) ? 1 : 0;
        
        $new_input['brand_color']    = sanitize_hex_color( $input['brand_color'] );
        if ( ! $new_input['brand_color'] ) $new_input['brand_color'] = '#ff0000';

        $new_input['enable_privacy_text'] = isset( $input['enable_privacy_text'] ) ? 1 : 0;
        $new_input['privacy_text']        = sanitize_text_field( $input['privacy_text'] );
        
        return $new_input;
    }

    public static function process_reset_stats() {
        if ( isset( $_GET['wpes_reset_stats'] ) && current_user_can( 'manage_options' ) ) {
            check_admin_referer( 'wpes_reset_action' );
            
            update_option( 'wpes_carbon_saved', 0 );
            update_option( 'wpes_total_plays', 0 );
            
            wp_safe_redirect( add_query_arg( 'wpes_msg', 'reset', remove_query_arg( ['wpes_reset_stats', '_wpnonce'] ) ) );
            exit;
        }
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php 
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( isset( $_GET['wpes_msg'] ) && $_GET['wpes_msg'] === 'reset' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Statistics successfully reset to zero.', 'eco-shield' ); ?></p>
                </div>
            <?php endif; ?>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'wpes_options_group' );
                do_settings_sections( 'eco-shield' );
                submit_button( 'Save Settings' );
                ?>
            </form>
            
            <hr style="margin-top: 30px;">
            
            <div style="display: flex; align-items: center; justify-content: space-between; background: #fff; padding: 15px; border: 1px solid #c3c4c7; border-left: 4px solid #008a20; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <div>
                    <strong style="font-size: 14px;"><?php esc_html_e( 'Lifetime Impact:', 'eco-shield' ); ?></strong> 
                    <span style="font-size: 14px; color: #008a20; margin-left: 5px;">
                        <?php 
                            $bytes = get_option( 'wpes_carbon_saved', 0 ); 
                            echo esc_html( round( $bytes / 1048576, 2 ) ); 
                        ?> MB
                    </span> 
                    <span style="color: #646970; margin: 0 5px;">|</span>
                    <span style="font-size: 14px; color: #008a20;">
                        <?php echo esc_html( get_option( 'wpes_total_plays', 0 ) ); ?> Plays
                    </span>
                </div>
                
                <div>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpes_reset_stats', '1' ), 'wpes_reset_action' ) ); ?>" 
                       class="button button-secondary" 
                       onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all stats to zero?', 'eco-shield' ); ?>');"
                       style="color: #d63638; border-color: #d63638;">
                        <?php esc_html_e( 'Reset Data', 'eco-shield' ); ?>
                    </a>
                </div>
            </div>

            <p style="margin-top: 20px; font-size: 13px; color: #646970; border-top: 1px solid #dcdcde; padding-top: 15px;">
                <?php 
                printf(
                    /* translators: %s: Link to reviews page with 5 stars */
                    esc_html__( 'Enjoying Eco-Shield? Please %s on WordPress.org to help us grow!', 'eco-shield' ),
                    '<a href="https://wordpress.org/support/plugin/eco-shield/reviews/#new-post" target="_blank" rel="noopener noreferrer">' . esc_html__( 'rate us ★★★★★', 'eco-shield' ) . '</a>'
                ); 
                ?>
            </p>
        </div>
        <?php
    }
}

WPES_Shield_Settings::init();