<?php

namespace Coditive\Form;

use WP_REST_Request, WP_Error;

class Endpoint
{
    /**
     * The endpoint's route it listens to.
     * 
     * @var array
     */
    protected $route;

    /**
     * The post type's key this endpoint writes to.
     * 
     * @var string
     */
    protected $postType;

    /**
     * The meta field's key this endpoint writes to.
     * 
     * @var string
     */
    protected $metaField;

    /**
     * A list of tax rate options for validation.
     * 
     * @var array
     */
    protected $taxRateOptions;

    /**
     * Initialize endpoint.
     * 
     * @param  array $attributes
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->route = $attributes['route'];
        $this->postType = $attributes['postType'];
        $this->metaField = $attributes['metaField'];
        $this->taxRateOptions = $attributes['taxRateOptions'];
    }

    /**
     * Register REST API route.
     * 
     * @return void
     * 
     * @see https://developer.wordpress.org/reference/functions/register_rest_route/
     */
    public function register(): void
    {
        register_rest_route($this->route['namespace'], $this->route['base'], [
            'methods' => 'POST',
            'callback' => [$this, 'handleRequest'],
            'args' => [
                'name' => [
                    'validate_callback' => [$this, 'validateName'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'price' => [
                    'validate_callback' => [$this, 'validatePrice'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'currency' => [
                    'validate_callback' => [$this, 'validateCurrency'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'taxRate' => [
                    'validate_callback' => [$this, 'validateTaxRate'],
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
    }

    /**
     * Validate name field.
     * 
     * @param  mixed $name
     * @return WP_Error|bool
     */
    public function validateName($name)
    {
        if (empty($name)) {
            return new WP_Error('name', 'Wprowadź nazwę produktu');
        }
        return true;
    }

    /**
     * Validate price field.
     * 
     * @param  mixed $price
     * @return WP_Error|bool
     */
    public function validatePrice($price)
    {
        if (empty($price)) {
            return new WP_Error('price', 'Wprowadź kwotę netto produktu');
        }
        if (! preg_match("/^([1-9]\d*|[0]),?(,\d{1,2})?$/", $price)) {
            return new WP_Error('price', 'Nieprawidłowy format kwoty netto');
        }
        return true;
    }

    /**
     * Validate currency field.
     * 
     * @param  mixed $currency
     * @return WP_Error|bool
     */
    public function validateCurrency($currency)
    {
        if (empty($currency)) {
            return new WP_Error('currency', 'Wybierz walutę');
        }
        if ($currency !== 'PLN') {
            return new WP_Error('currency', 'Wybrana waluta jest nieprawidłowa');
        }
        return true;
    }

    /**
     * Validate tax rate field.
     * 
     * @param  mixed $taxRate
     * @return WP_Error|bool
     */
    public function validateTaxRate($taxRate)
    {
        if (empty($taxRate)) {
            return new WP_Error('taxRate', 'Wybierz stawkę VAT');
        }
        if (! in_array($taxRate, $this->taxRateOptions)) {
            return new WP_Error('taxRate', 'Wybrana stawka VAT jest nieprawidłowa');
        }
        return true;
    }

    /**
     * Handle request.
     * 
     * @param  WP_REST_Request $request
     * @return mixed
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        [$price, $tax, $total] = $this->calculate($data['price'], $data['taxRate']);
        
        $this->save([
            'ip'       => $_SERVER['REMOTE_ADDR'],
            'date'     => date("d.m.Y"),
            'name'     => $data['name'],
            'price'    => $price,
            'currency' => $data['currency'],
            'tax_rate' => $data['taxRate'],
            'tax'      => $tax,
            'total'    => $total,
        ]);

        return "Cena produktu {$data['name']}, wynosi: {$total} zł brutto, kwota podatku to {$tax} zł.";
    }

    /**
     * Calculate tax ammount and total price.
     * 
     * @param  string $price
     * @param  string $taxRate
     * @return array
     */
    protected function calculate(string $price, string $taxRate): array
    {
        $tax = 0;
        $price = number_format(str_replace(',', '.', $price), 2, '.', '');
        $total = $price;

        if (! in_array($taxRate, ['0%', 'zw.', 'np.', 'o.o.'])) {
            $taxRate = number_format(str_replace('%', '', $taxRate), 0, '.', '');

            $tax = $price * ($taxRate / 100);
            $total += $tax;
        }

        return array_map(function($value) {
            return number_format($value, 2, ',', '');
        }, [$price, $tax, $total]);
    }

    /**
     * Save data to custom post type.
     * 
     * @param  array $data
     * @return void
     */
    protected function save(array $data): void
    {
        $post = [
            'post_title' => $data['name'],
            'post_status' => 'publish',
            'post_type' => $this->postType,
            'post_category' => ['uncategorized'],
            'post_author' => 0,
            'meta_input' => [$this->metaField => $data]
        ];

        wp_insert_post($post);
    }
}