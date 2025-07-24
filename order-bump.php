<?php
/*
Plugin Name: Order Bump Custom
Description: Plugin para adicionar oferta bump no checkout.
Version: 1.0
Author: Laura Barauna
*/

if (!defined('ABSPATH')) {
    exit;
}


function get_products_bump_relations() {
    return [
        61 => 66,
        40 => 13,
    ];
}

// Adiciona o checkbox de order bump no checkout com nome e preço dinâmicos
add_action('woocommerce_review_order_before_submit', 'adicionar_order_bump_checkout', 15);
function adicionar_order_bump_checkout() {
	$productsBump = get_products_bump_relations();
	
	$cart_items = WC()->cart->get_cart();
	$first_item = reset($cart_items); // pega o primeiro item
	$product_id = $first_item['product_id'];
	
	$bump_id = null; // Coloque aqui o ID do produto bump
	
	foreach($productsBump AS $key => $value) {
		if ($product_id == $key) {
			$bump_id = $value;
			break;
		}
	}
	
	if (!$bump_id) return;
	
    $product = wc_get_product($bump_id);
    if (!$product) return;

    $product_name = $product->get_name();
    $product_price = wc_price($product->get_price());
	$image_url = wp_get_attachment_url($product->get_image_id());
	
echo '<style>
    .container {
        display: flex;
        justify-content: center;
        align-content: center;
        align-items: center;
        flex-direction: column;
    }

    .product {
        display: flex;
        align-items: center;
        background-color: #ffffff; /* fundo branco */
        padding: 20px;
        color: #1c1c1c;
        border-radius: 8px;
        margin-bottom: 30px;
        border: 2px solid #1c1c1c;; 
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); /* leve sombra */
    }

    .product img {
        width: 80px;
        height: auto;
        margin-right: 15px;
        border-radius: 12px;
    }

    .text {
        text-align: center;
        margin-bottom: 25px;
        color: #1c1c1c;
    }

    .product label {
        font-size: 16px;
        line-height: 1.4;
        color: #e0e0e0;
    }

    .product strong {
        color: #4dc0e2; /* destaque azul claro */
    }

    input[type="checkbox"] {
        transform: scale(1.3);
        margin-right: 8px;
        accent-color: #4dc0e2;
    }

</style>';

		
	echo '<div class="container">';
		
	echo '<div class="text">';
	echo '<h3> <strong> OFERTA ÚNICA: </strong> </h3>';
	echo '<p><strong> [ATENÇÃO] </strong> Essas ofertas não estão disponíveis para compra em nenhum outro lugar!</p>';
	echo '</div>';
	
    echo '<div class="product">';

    if ($image_url) {
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product_name) . '" style="width: 80px; height: auto; margin-right: 15px; border-radius: 15px;">';
    }

    woocommerce_form_field('order_bump', array(
        'type'  => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => "Adicionar <strong>{$product_name}</strong> por apenas <strong>{$product_price}</strong>",
    ), WC()->checkout->get_value('order_bump'));

    echo '</div>';
	echo '</div>';

    ?>
    <script type="text/javascript">
    jQuery(function($){
        $('#order_bump').on('change', function(){
            $('body').trigger('update_checkout');
        });
    });
    </script>
    <?php
}

// Se o checkbox for marcado, adiciona o produto ao carrinho
add_action('woocommerce_checkout_update_order_review', 'adicionar_produto_bump_ao_carrinho');
function adicionar_produto_bump_ao_carrinho($post_data) {
    parse_str($post_data, $parsed_data);
    $bump_ativo = isset($parsed_data['order_bump']) && $parsed_data['order_bump'] === '1';

	$productsBump = get_products_bump_relations();
	
	$cart_items = WC()->cart->get_cart();
	$first_item = reset($cart_items); // pega o primeiro item
	$product_id = $first_item['product_id'];
	
	$bump_id = null; // Coloque aqui o ID do produto bump
	
	foreach($productsBump AS $key => $value) {
		if ($product_id == $key) {
			$bump_id = $value;
			break;
		}
	}
	
	if (!$bump_id) return;

    // Remove o produto do bump caso esteja no carrinho
    foreach (WC()->cart->get_cart() as $cart_item_key => $item) {
        if ($item['product_id'] == $bump_id) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }

    // Se marcado, adiciona o produto ao carrinho
    if ($bump_ativo) {
        WC()->cart->add_to_cart($bump_id);
    }
}


