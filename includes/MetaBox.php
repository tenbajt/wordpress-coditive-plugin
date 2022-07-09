<?php

namespace Coditive\Form;

use WP_Post;

class Metabox
{
    /**
     * Meta box id which also acts as the root element id for render.
     * 
     * @var string
     */
    protected const ID = 'cdtv-form-metabox';

    /**
     * Post type key this meta box is assigned to.
     * 
     * @var string
     */
    protected $postType;

    /**
     * Meta field key this meta box reads data from.
     * 
     * @var string
     */
    protected $metaField;

    /**
     * Initialize meta box.
     * 
     * @param  string $postType
     * @param  string $metaField
     * @return void
     */
    public function __construct(string $postType, string $metaField)
    {
        $this->postType = $postType;
        $this->metaField = $metaField;
    }

    /**
     * Register meta box.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/add_meta_box/
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/
     */
    public function register(): void
    {
        add_meta_box(self::ID, '', [$this, 'render'], $this->postType, 'normal');
        wp_register_script(self::ID, $this->getScriptUrl(), ['wp-element'], '1.0.0', true);
    }

    /**
     * Render meta box.
     * 
     * @param  WP_Post $post 
     * @param  array   $args
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function render(WP_Post $post, array $args): void
    {
        wp_localize_script(self::ID, 'props', $this->getScriptProps($post));
        wp_enqueue_script(self::ID);
    }

    /**
     * Return props used by render script.
     * 
     * @param  WP_Post $post
     * @return array
     * 
     * @see https://developer.wordpress.org/reference/functions/get_post_meta/
     */
    protected function getScriptProps(WP_Post $post): array
    {
        $meta = get_post_meta($post->ID, $this->metaField, true);
        $props = [
            'id' => self::ID,
            'data' => $meta,
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
        return WP_PLUGIN_URL . '/coditive-form/admin/build/index.js';
    }
}