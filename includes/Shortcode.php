<?php

namespace Coditive\Form;

class Shortcode
{
    /**
     * Shortcode tag which also acts as the root element id for render.
     * 
     * @var string
     */
    protected const ID = 'cdtv-form-shortcode';

    /**
     * Initialize shortcode.
     * 
     * @return void
     */
    public function __construct()
    {
        // No external data needed.
    }

    /**
     * Register shortcode.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/add_shortcode/
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/
     */
    public function register(): void
    {
        add_shortcode(self::ID, [$this, 'render']);
        wp_register_script(self::ID, $this->getScriptUrl(), ['wp-element'], '1.0.0', true);
    }

    /**
     * Render shortcode.
     * 
     * @return string
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function render(): string
    {
        wp_localize_script(self::ID, 'props', $this->getScriptProps());
        wp_enqueue_script(self::ID);

        $id = self::ID;

        return "<div id=\"{$id}\"></div>";
    }

    /**
     * Enqueue props used by render script.
     * 
     * @return array
     */
    protected function getScriptProps(): array
    {
        $nonce = wp_create_nonce(self::ID);
        $props = [
            'id' => self::ID,
            'nonce' => $nonce
        ];
        return $props;
    }

    /**
     * Return render script url.
     * 
     * @return string
     * 
     * @see https://developer.wordpress.org/reference/functions/plugin_dir_url/
     */
    protected function getScriptUrl(): string
    {
        return WP_PLUGIN_URL . '/coditive-form/frontend/build/index.js';
    }
}