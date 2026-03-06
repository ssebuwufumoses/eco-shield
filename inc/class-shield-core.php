<?php
/**
 * Eco-Shield Core Interceptor
 * Version: 1.2.1
 * Feature: Local Cache + WebP + Shorts + Legacy Optimizer + Widget + i18n + Deep Link + RSS Fix + Native Lazy + Privacy Overlay + Lightbox + Analytics
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPES_Shield_Core {

    public static function init() {
        $options = get_option( 'wpes_options' );
        $show_bar = isset( $options['show_admin_bar'] ) ? $options['show_admin_bar'] : 1;
        if ( $show_bar ) {
            add_action( 'admin_bar_menu', [ __CLASS__, 'add_toolbar_link' ], 999 );
        }

        // Feature 4: AJAX Handler for Analytics
        add_action( 'wp_ajax_wpes_track_play', [ __CLASS__, 'ajax_track_play' ] );
        add_action( 'wp_ajax_nopriv_wpes_track_play', [ __CLASS__, 'ajax_track_play' ] );

        if ( is_admin() || ( defined('REST_REQUEST') && REST_REQUEST ) ) {
            if ( is_admin() ) {
                $show_widget = isset( $options['show_widget'] ) ? $options['show_widget'] : 1;
                $show_toast  = isset( $options['show_toast'] ) ? $options['show_toast'] : 1;
                if ( $show_widget ) add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_dashboard_widget' ] );
                add_action( 'admin_init', [ __CLASS__, 'process_cache_purge' ] );
                if ( $show_toast ) add_action( 'admin_notices', [ __CLASS__, 'show_admin_notice' ] );
            }
            return;
        }

        add_filter( 'the_content', [ __CLASS__, 'intercept_iframes' ], 99 );
        add_filter( 'render_block', [ __CLASS__, 'shield_video_blocks' ], 10, 2 );
        add_filter( 'embed_oembed_html', [ __CLASS__, 'intercept_oembeds' ], 10, 3 );
        add_filter( 'widget_text', [ __CLASS__, 'intercept_iframes' ], 99 );
        add_filter( 'widget_custom_html_content', [ __CLASS__, 'intercept_iframes' ], 99 );
        
        $show_toast = isset( $options['show_toast'] ) ? $options['show_toast'] : 1;
        add_action( 'init', [ __CLASS__, 'process_cache_purge' ] );
        if ( $show_toast ) add_action( 'wp_footer', [ __CLASS__, 'show_frontend_notice' ] );
    }

    public static function ajax_track_play() {
        check_ajax_referer( 'wpes_track_play', 'nonce' );
        $current = (int) get_option( 'wpes_total_plays', 0 );
        update_option( 'wpes_total_plays', $current + 1, false );
        wp_send_json_success();
    }

    public static function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'wpes_carbon_widget',
            esc_html__( 'Eco-Shield Impact', 'eco-shield' ),
            [ __CLASS__, 'render_dashboard_widget' ]
        );
    }

    public static function render_dashboard_widget() {
        $bytes_saved = get_option( 'wpes_carbon_saved', 0 );
        $total_plays = get_option( 'wpes_total_plays', 0 );
        $mb_saved = $bytes_saved / 1048576;
        $grams_co2 = $mb_saved * 0.5; 
        ?>
        <div class="wpes-dashboard-stats" style="display: flex; gap: 20px; align-items: center; padding-top: 10px;">
            <div style="text-align: center; flex: 1;">
                <span class="dashicons dashicons-database" style="font-size: 32px; height: 32px; color: #008a20; margin-bottom: 5px;"></span>
                <h3 style="margin: 5px 0; font-size: 24px; color: #1d2327;">
                    <?php echo esc_html( round( $mb_saved, 2 ) ); ?> 
                    <span style="font-size: 14px; color: #646970;">MB</span>
                </h3>
                <p style="margin: 0; color: #646970; font-size: 12px;"><?php esc_html_e( 'Saved', 'eco-shield' ); ?></p>
            </div>
            <div style="height: 40px; border-left: 1px solid #dcdcde;"></div>
            <div style="text-align: center; flex: 1;">
                <span class="dashicons dashicons-controls-play" style="font-size: 32px; height: 32px; color: #008a20; margin-bottom: 5px;"></span>
                <h3 style="margin: 5px 0; font-size: 24px; color: #1d2327;">
                    <?php echo esc_html( number_format_i18n( $total_plays ) ); ?>
                </h3>
                <p style="margin: 0; color: #646970; font-size: 12px;"><?php esc_html_e( 'Plays', 'eco-shield' ); ?></p>
            </div>
            <div style="height: 40px; border-left: 1px solid #dcdcde;"></div>
            <div style="text-align: center; flex: 1;">
                <span class="dashicons dashicons-leaf" style="font-size: 32px; height: 32px; color: #008a20; margin-bottom: 5px;"></span>
                <h3 style="margin: 5px 0; font-size: 24px; color: #1d2327;">
                    <?php echo esc_html( round( $grams_co2, 3 ) ); ?> 
                    <span style="font-size: 14px; color: #646970;">g</span>
                </h3>
                <p style="margin: 0; color: #646970; font-size: 12px;"><?php esc_html_e( 'CO2 Saved', 'eco-shield' ); ?></p>
            </div>
        </div>
        <p style="margin-top: 20px; text-align: right; font-size: 11px; color: #a7aaad;">
            <i><?php esc_html_e( '*Est. based on 0.5g CO2 per MB', 'eco-shield' ); ?></i>
        </p>
        <?php
    }

    public static function add_toolbar_link( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top: -3px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>';
        $purge_url = wp_nonce_url( add_query_arg( 'wpes_purge_cache', '1' ), 'wpes_purge_action' );
        
        $wp_admin_bar->add_node( array(
            'id'    => 'wpes_purge',
            'title' => '<span class="ab-icon" style="display:flex; align-items:center; height:100%;">' . $icon_svg . '</span> ' . esc_html__( 'Purge Eco-Shield', 'eco-shield' ),
            'href'  => $purge_url,
            'meta'  => array( 'title' => esc_html__( 'Delete all cached thumbnails', 'eco-shield' ) )
        ));
    }

    public static function process_cache_purge() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpes_purge_cache'] ) && current_user_can( 'manage_options' ) ) {
            check_admin_referer( 'wpes_purge_action' );
            $upload_dir = wp_upload_dir();
            $cache_dir_path = $upload_dir['basedir'] . '/eco-shield-thumbs';
            if ( is_dir( $cache_dir_path ) ) {
                $files = glob( $cache_dir_path . '/*' );
                foreach ( $files as $file ) { if ( is_file( $file ) ) wp_delete_file( $file ); }
            }
            if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();
            if ( class_exists( 'autoptimizeCache' ) ) autoptimizeCache::clearall();
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            if ( class_exists( 'LiteSpeed\Purge' ) ) do_action( 'litespeed_purge_all' );
            
            if ( function_exists( 'w3tc_flush_all' ) ) w3tc_flush_all();

            $redirect_url = remove_query_arg( [ 'wpes_purge_cache', '_wpnonce' ] ); 
            $redirect_url = add_query_arg( 'wpes_purged', '1', $redirect_url );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    public static function show_admin_notice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpes_purged'] ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                    <span><strong>Eco-Shield:</strong> <?php esc_html_e( 'Cache cleared.', 'eco-shield' ); ?></span>
                </p>
            </div>
            <?php
        }
    }

    public static function show_frontend_notice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpes_purged'] ) && current_user_can( 'manage_options' ) ) {
            ?>
            <div id="wpes-toast" style="position: fixed; top: 50px; left: 50%; transform: translateX(-50%); background: #fff; border-left: 6px solid #008a20; padding: 16px 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); z-index: 999999; display: flex; align-items: center; gap: 12px; border-radius: 6px; min-width: 320px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; animation: wpes-drop-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                <span class="dashicons dashicons-yes-alt" style="color: #008a20; font-size: 24px;"></span>
                <span style="font-size: 14px; color: #1d2327; font-weight: 600;"><?php esc_html_e( 'Eco-Shield: Cache Purged!', 'eco-shield' ); ?></span>
            </div>
            <style>@keyframes wpes-drop-in { from { top: 0px; opacity: 0; } to { top: 50px; opacity: 1; } }</style>
            <script>setTimeout(function(){var t=document.getElementById('wpes-toast');if(t){t.style.opacity='0';t.style.top='20px';t.style.transition='all 0.5s';setTimeout(()=>t.remove(),500);}},4000);if(window.history.replaceState){const u=new URL(window.location);u.searchParams.delete('wpes_purged');window.history.replaceState({},'',u);}</script>
            <?php
        }
    }

    private static function extract_video_info( $url ) {
        $query_args = [];
        $parsed_url = wp_parse_url( html_entity_decode($url) );
        if ( isset( $parsed_url['query'] ) ) parse_str( $parsed_url['query'], $query_args );
        
        $params = [];
        if ( isset( $query_args['t'] ) ) $params['start'] = $query_args['t'];
        if ( isset( $query_args['start'] ) ) $params['start'] = $query_args['start'];
        if ( isset( $query_args['list'] ) ) $params['list'] = $query_args['list'];
        
        if ( isset( $parsed_url['fragment'] ) ) {
            $fragment = [];
            parse_str( $parsed_url['fragment'], $fragment );
            if ( isset( $fragment['t'] ) ) $params['start'] = $fragment['t'];
        }

        if ( strpos( $url, 'vimeo' ) !== false ) {
            preg_match( '/vimeo\.com\/([0-9]{6,11})/', $url, $m );
            return isset($m[1]) ? ['provider' => 'vimeo', 'id' => $m[1], 'type' => 'normal', 'params' => $params] : false;
        }
        preg_match( '/(v=|embed\/|youtu.be\/|shorts\/|live\/)([a-zA-Z0-9_-]{11})/', $url, $m );
        if ( isset($m[2]) ) {
            $is_short = ( strpos($url, '/shorts/') !== false );
            return ['provider' => 'youtube', 'id' => $m[2], 'type' => ($is_short ? 'short' : 'normal'), 'params' => $params];
        }
        return false;
    }

    public static function shield_video_blocks( $block_content, $block ) {
        $video_blocks = ['core-embed/youtube', 'core-embed/vimeo', 'core/embed'];
        if ( in_array($block['blockName'], $video_blocks) ) {
            $url = $block['attrs']['url'] ?? '';
            $data = self::extract_video_info( $url );
            if ( $data ) return self::render_placeholder( [ '', $data['provider'], $data['id'] ], $data['type'], $data['params'] );
        }
        return $block_content;
    }

    public static function intercept_iframes( $content ) {
        $pattern = '/<iframe.*?src=".*?(youtube\.com\/embed\/|youtube-nocookie\.com\/embed\/|youtube\.com\/shorts\/|youtube\.com\/live\/|player\.vimeo\.com\/video\/)(.*?)(\?.*?)?".*?><\/iframe>/i';
        return preg_replace_callback( $pattern, function($m) {
             $type = (strpos($m[0], '/shorts/') !== false) ? 'short' : 'normal';
             $full_src = 'https://' . $m[1] . $m[2] . ($m[3] ?? '');
             $data = self::extract_video_info( $full_src );
             $params = $data ? $data['params'] : [];
             return self::render_placeholder($m, $type, $params);
        }, $content );
    }

    public static function intercept_oembeds( $html, $url, $attr ) {
        $data = self::extract_video_info( $url );
        if ( $data ) return self::render_placeholder( [ '', $data['provider'], $data['id'] ], $data['type'], $data['params'] );
        return $html;
    }

    private static function get_cached_thumbnail( $provider, $video_id ) {
        $upload_dir = wp_upload_dir();
        $cache_dir_path = $upload_dir['basedir'] . '/eco-shield-thumbs';
        $cache_dir_url  = $upload_dir['baseurl'] . '/eco-shield-thumbs';
        if ( ! file_exists( $cache_dir_path ) ) { wp_mkdir_p( $cache_dir_path ); file_put_contents( $cache_dir_path . '/index.php', '<?php // Silence.' ); }
        $filename_base = "{$provider}-{$video_id}";
        $path_webp = $cache_dir_path . '/' . $filename_base . '.webp';
        $url_webp = $cache_dir_url . '/' . $filename_base . '.webp';
        $path_jpg = $cache_dir_path . '/' . $filename_base . '.jpg';
        $url_jpg = $cache_dir_url . '/' . $filename_base . '.jpg';
        if ( file_exists( $path_webp ) ) return $url_webp;
        if ( file_exists( $path_jpg ) ) return $url_jpg;

        $source_url = '';
        if ( 'youtube' === $provider ) {
            $source_url = 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg';
        } elseif ( 'vimeo' === $provider ) {
            $api_url = "https://vimeo.com/api/v2/video/{$video_id}.json";
            $response = wp_remote_get( $api_url );
            if ( ! is_wp_error( $response ) ) {
                $body = wp_remote_retrieve_body( $response );
                $data = json_decode( $body, true );
                if ( ! empty( $data[0]['thumbnail_large'] ) ) $source_url = $data[0]['thumbnail_large'];
            }
        }
        if ( empty( $source_url ) ) return '';
        $response = wp_remote_get( $source_url );
        if ( is_wp_error( $response ) ) return $source_url; 
        file_put_contents( $path_jpg, wp_remote_retrieve_body( $response ) );
        
        $editor = wp_get_image_editor( $path_jpg );
        if ( ! is_wp_error( $editor ) ) {
            $saved = $editor->save( $path_webp, 'image/webp' );
            if ( ! is_wp_error( $saved ) && isset($saved['path']) ) {
                $s_jpg = filesize( $path_jpg ); $s_webp = filesize( $path_webp );
                $bytes_saved = $s_jpg - $s_webp;
                if ( $bytes_saved > 0 ) {
                    $current_total = get_option( 'wpes_carbon_saved', 0 );
                    update_option( 'wpes_carbon_saved', $current_total + $bytes_saved, false );
                }
                wp_delete_file( $path_jpg );
                return $url_webp;
            }
        }
        return $url_jpg;
    }

    public static function render_placeholder( $matches, $type = 'normal', $params = [] ) {
        if ( count($matches) === 4 ) {
            $provider = (strpos($matches[1], 'vimeo') !== false) ? 'vimeo' : 'youtube';
            $video_id = $matches[2];
        } else {
            $provider = $matches[1];
            $video_id = $matches[2];
        }
        $thumbnail_url = self::get_cached_thumbnail( $provider, $video_id );
        if ( ! $thumbnail_url ) {
            $thumbnail_url = ('youtube' === $provider) ? 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg' : ''; 
        }

        if ( is_feed() ) {
            $watch_url = ('vimeo' === $provider) ? 'https://vimeo.com/' . $video_id : 'https://www.youtube.com/watch?v=' . $video_id;
            return sprintf( '<p><a href="%s" target="_blank" rel="noopener"><img src="%s" alt="%s" style="display:block; max-width:100%%; border-radius:8px;" /></a></p>', esc_url( $watch_url ), esc_url( $thumbnail_url ), esc_attr__( 'Play Video', 'eco-shield' ) );
        }

        $extra_class = ($type === 'short') ? 'wpes-vertical' : '';
        
        // Prepare variables for safe escaping
        $start_val = isset($params['start']) ? $params['start'] : '';
        $list_val  = isset($params['list']) ? $params['list'] : '';

        // Privacy Text
        $privacy_html = '';
        $options = get_option('wpes_options');
        if ( isset($options['enable_privacy_text']) && $options['enable_privacy_text'] == 1 ) {
             $raw_text = isset($options['privacy_text']) && !empty($options['privacy_text']) ? $options['privacy_text'] : 'By clicking, you agree to load content from %s.';
             $final_text = str_replace( '%s', ($provider === 'youtube' ? 'YouTube' : 'Vimeo'), $raw_text );
             $privacy_html = '<div class="wpes-privacy-notice">' . esc_html($final_text) . '</div>';
        }

        // Lightbox
        $is_lightbox = false;
        if ( isset($options['enable_lightbox']) && $options['enable_lightbox'] == 1 ) {
             $is_lightbox = true;
        }

        ob_start(); ?>
        <div class="wpes-placeholder <?php echo esc_attr($extra_class); ?>" 
             data-video-id="<?php echo esc_attr( $video_id ); ?>" 
             data-provider="<?php echo esc_attr( $provider ); ?>"
             <?php if ( $start_val ) : ?>data-start="<?php echo esc_attr( $start_val ); ?>"<?php endif; ?> 
             <?php if ( $list_val ) : ?>data-list="<?php echo esc_attr( $list_val ); ?>"<?php endif; ?>
             <?php if ( $is_lightbox ) : ?>data-mode="lightbox"<?php endif; ?>
             tabindex="0" role="button" aria-label="<?php esc_attr_e('Play Video', 'eco-shield'); ?>">
             
            <img src="<?php echo esc_url($thumbnail_url); ?>" 
                 alt="<?php esc_attr_e('Video Thumbnail', 'eco-shield'); ?>" 
                 loading="lazy" 
                 class="wpes-poster" />
            
            <div class="wpes-overlay"><div class="wpes-play-button"></div></div>
            <?php echo wp_kses_post( $privacy_html ); ?>
        </div>
        <?php return ob_get_clean();
    }
}
WPES_Shield_Core::init();