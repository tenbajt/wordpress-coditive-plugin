<?php
/**
 * Plugin Name:       Coditive Form
 * Plugin URI:        https://www.example.com/
 * Description:       Simple tax and total price calculator.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0.8
 * Author:            Szymon Mazurczak
 * Author URI:        https://www.example.pl
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       coditive-form
 * Domain Path:       /lang
 * Update URI:        https://www.example.pl/
 */

namespace Coditive\Form;

use WP_Post;

class Plugin
{
    /**
     * The post type's key.
     * 
     * @var string
     */
    protected $postType = 'cdtv_calculations';

    /**
     * The meta field's key.
     * 
     * @var string
     */
    protected $metaField = '_cdtv_form_data';

    /**
     * The meta box's id.
     * 
     * @var string
     */
    protected $metaBox = 'cdtv-form-metabox';

    /**
     * The shortcode's tag.
     * 
     * @var string
     */
    protected $shortcode = 'cdtv-form-shortcode';

    /**
     * A list of tax rate options.
     * 
     * @var array
     */
    protected $taxRateOptions = ['23%', '22%', '8%', '7%', '5%', '3%', '0%', 'zw.', 'np.', 'o.o.'];

    /**
     * The shortcode's endpoint route.
     * 
     * @var array
     */
    protected $endpointRoute = [
        'namespace' => 'coditive-form/v2',
        'base' => '/submit/'
    ];

    /**
     * Initialize plugin instance.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/hooks/init/
     * @see https://developer.wordpress.org/reference/hooks/rest_api_init/
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerScripts']);
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerMetaField']);
        add_action('init', [$this, 'registerShortcode']);
        add_action('rest_api_init', [$this, 'registerEndpoint']);
    }

    /**
     * Register scripts.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/plugin_dir_url/
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/
     */
    public function registerScripts(): void
    {
        $pluginUrl = plugin_dir_url(__FILE__);
        wp_register_script($this->metaBox, "{$pluginUrl}templates/metabox/build/index.js", ['wp-element'], '1.0.0', true);
        wp_register_script($this->shortcode, "{$pluginUrl}templates/shortcode/build/index.js", ['wp-element', 'wp-api-fetch'], '1.0.0', true);
    }

    /**
     * Register post type.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/register_post_type/
     */
    public function registerPostType(): void
    {
        $labels = [
            'name'         => 'Wyliczenia',
            'edit_item'    => 'Wyliczenie',
            'search_items' => 'Szukaj wyliczeń',
        ];

        $capabilities = [
            'create_posts'       => false,
            'edit_post'          => 'manage_options',
            'read_post'          => 'manage_options',
            'delete_post'        => 'manage_options',
            'edit_posts'         => 'manage_options',
            'edit_others_posts'  => 'manage_options',
            'publish_posts'      => false,
        ];

        $args = [
            'description'          => 'Historia wyliczeń',
            'menu_icon'            => 'dashicons-calculator',
            'menu_position'        => 30,
            'public'               => false,
            'publicly_queryable'   => false,
            'exclude_from_search'  => true,
            'show_ui'              => true,
            'show_in_admin_bar'    => false,
            'hierarchical'         => false,
            'has_archive'          => false,
            'rewrite'              => false,
            'can_export'           => false,
            'supports'             => ['title'],
            'capabilities'         => $capabilities,
            'labels'               => $labels,
            'register_meta_box_cb' => [$this, 'registerMetaBox']
        ];

        register_post_type($this->postType, $args);
    }

    /**
     * Register meta box.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/add_meta_box/
     */
    public function registerMetaBox(): void
    {
        add_meta_box($this->metaBox, 'Dane formularza', [$this, 'renderMetaBox']);
    }

    /**
     * Render meta box.
     * 
     * @param 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/get_post_meta/
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function renderMetaBox(WP_Post $post, array $args): void
    {
        wp_localize_script($this->metaBox, 'props', [
            'id' => $this->metaBox,
            'meta' => get_post_meta($post->ID, $this->metaField, true),
        ]);
        wp_enqueue_script($this->metaBox);
    }


    /**
     * Register meta field.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/register_post_meta/
     */
    public function registerMetaField(): void
    {
        register_post_meta($this->postType, $this->metaField, [
            'type'   => 'array',
            'single' => true
        ]);
    }

    /**
     * Register shortcode.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/add_shortcode/
     */
    public function registerShortcode(): void
    {
        add_shortcode($this->shortcode, [$this, 'renderShortcode']);
    }

    /**
     * Render shortcode.
     * 
     * @return string
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function renderShortcode(): string
    {
        wp_localize_script($this->shortcode, 'props', [
            'id' => $this->shortcode,
            'apiPath' => join($this->endpointRoute),
            'taxRateOptions' => $this->taxRateOptions
        ]);
        wp_enqueue_script($this->shortcode);

        return "<div id=\"{$this->shortcode}\"></div>";
    }

    /**
     * Register REST API route.
     * 
     * @return void
     */
    public function registerEndpoint(): void
    {
        $endpoint = new Endpoint([
            'route' => $this->endpointRoute,
            'postType' => $this->postType,
            'metaField' => $this->metaField,
            'taxRateOptions' => $this->taxRateOptions
        ]);
        $endpoint->register();
    }
}

require 'includes/vendor/autoload.php';

return new Plugin();