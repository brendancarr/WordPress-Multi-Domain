<?php
/*
Plugin Name: @Infinus Multi-Domain Support
Description: Allow WordPress to serve the same site content from multiple domains without redirection. Handles CORS and Headers. Use settings to set allowable domains.
Version: 1.2
Author: Infinus Technology
Author URI: https://infinus.ca
*/

defined('ABSPATH') or exit;

// Always use HTTPS + current domain for site/home URL
add_filter('pre_option_siteurl', function() {
    return 'https://' . $_SERVER['HTTP_HOST'];
});
add_filter('pre_option_home', function() {
    return 'https://' . $_SERVER['HTTP_HOST'];
});

// Disable canonical redirection
remove_filter('template_redirect', 'redirect_canonical');

add_filter('the_content', function($content) {
    $saved_siteurl = get_option('siteurl');
    $parsed = parse_url($saved_siteurl);
    if (!isset($parsed['host'])) return $content;

    $host = $parsed['host'];
    $search = [
        "http://$host",
        "https://$host"
    ];

    return str_replace($search, '', $content);
});


// Set a canonical tag using the current domain (always HTTPS)
add_action('wp_head', function() {
    $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    echo '<link rel="canonical" href="' . esc_url($current_url) . '" />' . "\n";
});


// Register settings menu
add_action('admin_menu', function() {
    add_options_page(
        'Multi-Domain Settings',
        'Multi-Domain',
        'manage_options',
        'multi-domain-settings',
        'multi_domain_settings_page'
    );
});

// Register setting
add_action('admin_init', function() {
    register_setting('multi_domain_settings_group', 'multi_domain_allowed_origins');
});

// Settings page UI
function multi_domain_settings_page() {
    ?>
    <div class="wrap">
        <h1>Multi-Domain Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('multi_domain_settings_group'); ?>
            <?php do_settings_sections('multi_domain_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Allowed Origins (one per line)</th>
                    <td>
                        <textarea name="multi_domain_allowed_origins" rows="8" cols="50"><?php
                            echo esc_textarea(get_option('multi_domain_allowed_origins'));
                        ?></textarea>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('init', function() {

    $origin = $_SERVER['SERVER_NAME'] ?? '';
    if (strpos($origin, 'www.') === 0) {
        $origin = substr($origin, 4);
    }

    $allowed_origins = explode("\n", get_option('multi_domain_allowed_origins', ''));
    $allowed_origins = array_map('trim', array_filter($allowed_origins));

    $font_exts = ['woff', 'woff2', 'ttf'];
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

    if (in_array($origin, $allowed_origins)) {
        if (!headers_sent()) {
            header("Access-Control-Allow-Origin: https://$origin");
            header("Vary: Origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");
            header("Access-Control-Expose-Headers: Content-Length, X-Knowledge-Base");
            header("Access-Control-Allow-Headers: *");
            header("Access-Control-Max-Age: 86400"); // cache preflight
            header("Access-Control-Allow-Private-Network: true"); // for Chrome local
        }
    }

    // Allow font MIME types
    if (in_array($ext, $font_exts)) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/font-' . $ext);
    }

    //need to add an area where it edits the .htaccess and adds:
    
    /*
    <FilesMatch "\.(ttf|otf|eot|woff|woff2)$">
        Header set Access-Control-Allow-Origin "*"
    </FilesMatch>
    */


});



add_action('send_headers', function() {
    $origin = $_SERVER['SERVER_NAME'] ?? '';
    if (strpos($origin, 'www.') === 0) {
        $origin = substr($origin, 4);
    }
    $allowed_origins = get_option('multi_domain_allowed_origins', '');
    $allowed_origins = array_filter(array_map('trim', explode("\n", $allowed_origins)));

    $font_exts = ['woff', 'woff2', 'ttf'];
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

    if (in_array($origin, $allowed_origins)) {

        header("Access-Control-Allow-Origin: https://$origin");
        header("Vary: Origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");
        header("Access-Control-Expose-Headers: Content-Length, X-Knowledge-Base");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Max-Age: 86400"); // cache preflight
        header("Access-Control-Allow-Private-Network: true"); // for Chrome local
    }

    // Allow font MIME types
    if (in_array($ext, $font_exts)) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/font-' . $ext);
    }
});

// Early preflight response
add_action('init', function () {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && isset($_SERVER['SERVER_NAME'])) {
        status_header(204);
        exit;
    }
}, 0);


// Force HTTPS for all links in the content and other URLs

function force_https_links($content) {
    $host = $_SERVER['HTTP_HOST'];
    return str_replace("http://$host", "https://$host", $content);
}

add_filter('the_content', 'force_https_links');
add_filter('widget_text', 'force_https_links');
add_filter('wp_nav_menu', 'force_https_links');
add_filter('script_loader_src', 'force_https_links');
add_filter('style_loader_src', 'force_https_links');
add_filter('template_directory_uri', 'force_https_links');
add_filter('stylesheet_directory_uri', 'force_https_links');
add_filter('home_url', 'force_https_links');
add_filter('site_url', 'force_https_links');
add_filter('content_url', 'force_https_links');
add_filter('plugins_url', 'force_https_links');