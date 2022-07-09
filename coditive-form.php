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

use Coditive\Form\Metabox;
use Coditive\Form\Shortcode;

class Plugin
{
    /**
     * Post type key.
     * 
     * @var string
     */
    const POST_TYPE = 'cdtv_calculations';

    /**
     * Meta field key.
     * 
     * @var string
     */
    const META_FIELD = '_cdtv_form_data';

    /**
     * The front-end script ID.
     * 
     * @var string
     */
    const FORM_ID = 'tenbajt_form';

    /**
     * The AJAX action ID.
     * 
     * @var string
     */
    const AJAX_ACTION_ID = 'tenbajt_form_submit';

    /**
     * A list of tax rate options.
     * 
     * @var array
     */
    const TAX_RATE_OPTIONS = [
        // TO-DO (for validation and as the only truth of source)
    ];

    /**
     * Plugin root directory URL.
     * 
     * @var string
     */
    protected $url;

    /**
     * Initialize plugin instance.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/hooks/init/
     * @see https://developer.wordpress.org/reference/hooks/add_meta_boxes/
     * @see https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
     * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
     * @see https://developer.wordpress.org/reference/hooks/wp_ajax_nopriv_action/
     */
    public function __construct()
    {
        $this->url = plugin_dir_url(__FILE__);

        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerMetaField']);
        add_action('init', [$this, 'registerShortcode']);
        add_action('add_meta_boxes_'.self::POST_TYPE, [$this, 'registerMetaBox']);

        // We need both, as which one fires depends on user authenthication status
        add_action('wp_ajax_'.self::AJAX_ACTION_ID, [$this, 'handleFormSubmit']);
        add_action('wp_ajax_nopriv_'.self::AJAX_ACTION_ID, [$this, 'handleFormSubmit']);
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
            'name'              => 'Wyliczenia',
            'view_item'         => 'Zobacz wyliczenia',
            'all_items'         => 'Wszystkie wyliczenia',
            'not_found'         => 'Nie znaleziono żadnych wyliczeń',
            'items_list'        => 'Lista wyliczeń',
            'view_items'        => 'Zobacz wyliczenia',
            'attributes'        => 'Atrybuty',
            'search_items'      => 'Szukaj wyliczeń',
            'singular_name'     => 'Wyliczenie',
            'filter_items_list' => 'Filtruj listę',
        ];

        $capabilities = [
            'create_posts'       => false,
            'edit_post'          => 'manage_options',
            'read_post'          => 'manage_options',
            'delete_post'        => 'manage_options',
            'edit_posts'         => 'manage_options',
            'edit_others_posts'  => 'manage_options',
            'publish_posts'      => false,
            'read_private_posts' => 'manage_options',
        ];

        $postType = [
            'description'         => 'Historia wyliczeń',
            'menu_icon'           => 'dashicons-calculator',
            'menu_position'       => 30,
            'public'              => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_admin_bar'   => false,
            'hierarchical'        => false,
            'has_archive'         => false,
            'rewrite'             => false,
            'can_export'          => false,
            'supports'            => ['title'],
            'capabilities'        => $capabilities,
            'labels'              => $labels
        ];

        register_post_type(self::POST_TYPE, $postType);
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
        register_post_meta(self::POST_TYPE, self::META_FIELD, [
            'type'   => 'array',
            'single' => true
        ]);
    }

    /**
     * Register shortcode.
     * 
     * @return void
     */
    public function registerShortcode(): void
    {
        $shortcode = new Shortcode();
        $shortcode->register();
    }

    /**
     * Register meta box.
     * 
     * TO-DO Args and script path
     * 
     * @return void
     */
    public function registerMetaBox(): void
    {
        $metaBox = new MetaBox(self::POST_TYPE, self::META_FIELD);
        $metabox->register();
    }

    /**
     * Register script.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     */
    public function registerScript(): void
    {
        wp_register_script(self::FORM_ID, plugin_dir_url(__FILE__).'frontend/build/index.js', ['wp-element'], '1.0.0');
        wp_localize_script(self::FORM_ID, 'props', [
            'id' => self::FORM_ID,
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::AJAX_ACTION_ID),
            'action' => self::AJAX_ACTION_ID
        ]);
    }

    /**
     * Handle form submission.
     * 
     * @return void
     */
    public function handleFormSubmit(): void
    {
        $data = array_map('sanitize_text_field', $_POST);
        $data = $this->validate($data);
        
        [$tax, $total] = $this->calculate($data['price'], $data['tax_rate']);

        $this->save([
            'ip'       => $_SERVER['REMOTE_ADDR'],
            'date'     => date("d.m.Y"),
            'name'     => $data['name'],
            'price'    => $data['price'],
            'currency' => $data['currency'],
            'tax_rate' => $data['tax_rate'],
            'tax'      => $tax,
            'total'    => $total,
        ]);

        [$tax, $total] = $this->parseNumbers([$tax, $total]);
        wp_send_json_success(['message' => "Cena produktu {$data['name']}, wynosi: {$total} zł brutto, kwota podatku to {$tax} zł."]);
    }

    /**
     * Validate form data.
     * 
     * @param  array $data
     * @return array
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_verify_nonce/
     * @see https://developer.wordpress.org/reference/functions/wp_send_json_error/
     */
    protected function validate(array $data): array
    {
        if (! wp_verify_nonce($data['nonce'], self::AJAX_ACTION_ID)) {
            wp_send_json_error(['nonce' => 'Twój formularz wygasł. Odświez stronę i spróbuj ponownie.']);
        }

        if (empty($data['name'])) {
            wp_send_json_error(['name' => 'Wprowadź nazwę produktu.']);
        }

        if (empty($data['price'])) {
            wp_send_json_error(['price' => 'Wprowadź kwotę netto produktu.']);
        }

        if (! preg_match("/^([1-9]\d*|[0]),?(,\d{1,2})?$/", $data['price'])) {
            wp_send_json_error(['price' => 'Nieprawidłowy format kwoty netto produktu.']);
        }

        if (empty($data['currency']) || $data['currency'] !== 'PLN') {
            wp_send_json_error(['currency' => 'Nieprawidłowa waluta.']);
        }

        // TO-DO Tax rate validation

        $data['price'] = number_format(str_replace(',', '.', $data['price']), 2, '.', '');

        return $data;
    }

    /**
     * Perform calculations.
     * 
     * @param  float $price
     * @param  mixed $tax_rate
     * @return array
     */
    protected function calculate(float $price, $tax_rate): array
    {
        $tax = 0;
        $total = $price;

        if (! in_array($tax_rate, ['0', 'zw', 'np', 'oo'])) {
            $tax = $price * ($tax_rate / 100);
            $total += $tax;
        }

        return array_map(function($value) {
            return number_format($value, 2, '.', '');
        }, [$tax, $total]);
    }

    /**
     * Save form data to custom post type.
     * 
     * @param  array $data
     * @return void
     */
    protected function save(array $data): void
    {
        $post = [
            'post_title' => $data['name'],
            'post_status' => 'publish',
            'post_type' => self::POST_TYPE_ID,
            'post_category' => ['uncategorized'],
            'post_author' => 0,
            'meta_input' => [self::META_FIELD_ID => $data]
        ];

        wp_insert_post($post);
    }

    /**
     * Convert numbers to display format.
     * 
     * @param  array $values
     * @return array
     */
    protected function parseNumbers(array $values): array
    {
        return array_map(function($value) {
            return str_replace('.', ',', $value);
        }, $values);
    }

    /**
     * Return parse meta data to print in admin view.
     * 
     * @return array
     */
    protected function getMeta(): array
    {
        global $post;

        $meta = get_post_meta($post->ID, self::META_FIELD_ID, true);
        


        return [];
    }
}

require 'includes/vendor/autoload.php';

return new Plugin();