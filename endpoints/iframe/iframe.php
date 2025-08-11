<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in a WordPress environment
if (!function_exists('add_action')) {
    return;
}

class Blogstorm_Iframe_Handler {
    private static $instance = null;
    const VERSION = '1.0.0';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register shortcode and content filter if we're in WordPress
        if (function_exists('add_shortcode')) {
            add_shortcode('blogstorm_iframe', array($this, 'render_iframe'));
        }
        if (function_exists('add_filter')) {
            add_filter('the_content', array($this, 'parse_iframe_in_content'), 10);
        }
    }

    public function render_iframe($atts) {
        // Default attributes
        $defaults = array(
            'width' => '100%',
            'height' => '500',
            'scrolling' => 'yes',
            'class' => 'blogstorm-iframe',
            'frameborder' => '0',
            'allowfullscreen' => 'true'
        );

        if (!is_array($atts)) {
            $atts = array();
        }

        // Merge with defaults using wp_parse_args if available
        $atts = function_exists('wp_parse_args') ? wp_parse_args($atts, $defaults) : array_merge($defaults, $atts);

        $html = "\n" . '<!-- Blogstorm iframe v.' . self::VERSION . ' -->' . "\n";
        $html .= '<iframe';
        
        foreach ($atts as $attr => $value) {
            // Sanitize URL if WordPress function exists
            if (strtolower($attr) == 'src') {
                $value = function_exists('esc_url') ? esc_url($value) : filter_var($value, FILTER_SANITIZE_URL);
            }

            // Skip potentially dangerous attributes
            if (strtolower($attr) == 'srcdoc' || strpos(strtolower($attr), 'on') === 0) {
                continue;
            }

            // Sanitize attribute values
            $sanitize_value = function($val) {
                if (function_exists('esc_attr')) {
                    return esc_attr($val);
                }
                return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            };

            if ($value !== '') {
                $html .= ' ' . $sanitize_value($attr) . '="' . $sanitize_value($value) . '"';
            } else {
                $html .= ' ' . $sanitize_value($attr);
            }
        }
        
        $html .= '></iframe>' . "\n";

        // Add responsive height adjustment if specified
        if (isset($atts['same_height_as'])) {
            $sanitize_js = function($val) {
                if (function_exists('esc_js')) {
                    return esc_js($val);
                }
                return addslashes($val);
            };

            $html .= sprintf(
                '<script>
                document.addEventListener("DOMContentLoaded", function(){
                    var target_element = document.querySelector("%s");
                    var iframe_element = document.querySelector("iframe.%s");
                    if (target_element && iframe_element) {
                        iframe_element.style.height = target_element.offsetHeight + "px";
                    }
                });
                </script>',
                $sanitize_js($atts['same_height_as']),
                $sanitize_js($atts['class'])
            );
        }

        return $html;
    }

    public function parse_iframe_in_content($content) {
        // Regular expression to find iframe tags
        $pattern = '/<iframe[^>]*>(.*?)<\/iframe>/i';
        
        return preg_replace_callback($pattern, array($this, 'convert_iframe_to_shortcode'), $content);
    }

    private function convert_iframe_to_shortcode($matches) {
        $iframe = $matches[0];
        
        // Extract attributes
        $attributes = array();
        preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $iframe, $attr_matches, PREG_SET_ORDER);
        
        foreach ($attr_matches as $attr_match) {
            $attributes[$attr_match[1]] = $attr_match[2];
        }
        
        // Build shortcode attributes string with fallback sanitization
        $sanitize_attr = function($val) {
            if (function_exists('esc_attr')) {
                return esc_attr($val);
            }
            return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        };

        $shortcode_attrs = '';
        foreach ($attributes as $key => $value) {
            $shortcode_attrs .= sprintf(' %s="%s"', $sanitize_attr($key), $sanitize_attr($value));
        }
        
        return sprintf('[blogstorm_iframe%s]', $shortcode_attrs);
    }
}

// Initialize the iframe handler if we're in WordPress
if (function_exists('add_action')) {
    add_action('init', array('Blogstorm_Iframe_Handler', 'get_instance'));
} 