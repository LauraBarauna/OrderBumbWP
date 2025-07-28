<?php

class OrderBump {
    public function __construct() {
        add_action('woocommerce_review_order_before_submit', [$this, 'adicionarOrderBumpCheckout'], 15);
        add_action('woocommerce_checkout_update_order_review', [$this, 'adicionarProdutoBumpAoCarrinho']);
        add_action('wfacp_enqueue_scripts', [$this, 'registrarEstilos'], 999);
        add_action('wp_enqueue_scripts', [$this, 'registrarEstilos']);
    }

    public function registrarEstilos() {
        wp_enqueue_style('order-bump-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
    }

    private function getProductsBumpRelations() {
        return [
            13 => 24,
            40 => 13,
        ];
    }

    public function adicionarOrderBumpCheckout() {
        $productsBump = $this->getProductsBumpRelations();
        $cart_items = WC()->cart->get_cart();
        $first_item = reset($cart_items);
        $product_id = $first_item['product_id'] ?? null;

        $bump_id = $productsBump[$product_id] ?? null;

        if (!$bump_id) return;

        $product = wc_get_product($bump_id);
        if (!$product) return;

        $product_name = $product->get_name();
        $product_price = wc_price($product->get_price());
        $image_url = wp_get_attachment_url($product->get_image_id());

        $this->renderBumpHtml($product_name, $product_price, $image_url);
    }

    private function renderBumpHtml($name, $price, $image_url) {
        echo '<style>/* mesmo CSS do seu código */</style>';

        echo '<div class="container">';
        echo '<div class="text">';
        echo '<h3> <strong> OFERTA ÚNICA: </strong> </h3>';
        echo '<p><strong> [ATENÇÃO] </strong> Essas ofertas não estão disponíveis para compra em nenhum outro lugar!</p>';
        echo '</div>';

        echo '<div class="product">';
        if ($image_url) {
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($name) . '">';
        }

        woocommerce_form_field('order_bump', array(
            'type'  => 'checkbox',
            'class' => array('form-row-wide'),
            'label' => "Adicionar <strong>{$name}</strong> por apenas <strong>{$price}</strong>",
        ), WC()->checkout->get_value('order_bump'));

        echo '</div></div>';

        echo "<script>jQuery(function($){ $('#order_bump').on('change', function(){ $('body').trigger('update_checkout'); }); });</script>";
    }

    public function adicionarProdutoBumpAoCarrinho($post_data) {
        parse_str($post_data, $parsed_data);
        $bump_ativo = isset($parsed_data['order_bump']) && $parsed_data['order_bump'] === '1';

        $productsBump = $this->getProductsBumpRelations();
        $cart_items = WC()->cart->get_cart();
        $first_item = reset($cart_items);
        $product_id = $first_item['product_id'] ?? null;

        $bump_id = $productsBump[$product_id] ?? null;
        if (!$bump_id) return;

        foreach (WC()->cart->get_cart() as $cart_item_key => $item) {
            if ($item['product_id'] == $bump_id) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }

        if ($bump_ativo) {
            WC()->cart->add_to_cart($bump_id);
        }
    }
}
