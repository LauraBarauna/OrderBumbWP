<?php

class CheckoutFields {
    public function __construct() {
        add_filter('woocommerce_checkout_fields', [$this, 'adicionarCampoCPF']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'salvarCampoCPF']);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'mostrarCPFNoAdmin'], 10, 1);
        add_action('woocommerce_review_order_before_submit', [$this, 'forcarCampoCPFCheckout'], 5);
        add_action('wfacp_enqueue_scripts', [$this, 'registrarEstilos'], 999);
        add_action('wp_enqueue_scripts', [$this, 'registrarEstilos']);
    }

    public function registrarEstilos() {
        wp_enqueue_style('order-bump-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
    }
    

    public function adicionarCampoCPF($fields) {
        $fields['billing']['billing_cpf'] = array(
            'label'       => 'CPF',
            'placeholder' => 'Digite seu CPF',
            'required'    => true,
            'class'       => array('form-row-wide'),
            'clear'       => true,
            'priority'    => 25,
        );
        return $fields;
    }

    public function salvarCampoCPF($order_id) {
        if (!empty($_POST['billing_cpf'])) {
            update_post_meta($order_id, '_billing_cpf', sanitize_text_field($_POST['billing_cpf']));
        }
    }

    public function mostrarCPFNoAdmin($order) {
        $cpf = get_post_meta($order->get_id(), '_billing_cpf', true);
        if ($cpf) {
            echo '<p><strong>CPF:</strong> ' . esc_html($cpf) . '</p>';
        }
    }

    public function forcarCampoCPFCheckout() {
    $cpf = WC()->checkout()->get_value('billing_cpf');
    ?>
    <div id="div-cpf" class="form-row form-row-wide">
        <label for="billing_cpf">Por fim, por favor informe seu CPF <span class="required"></span></label>
        <input type="text" class="input-text" name="billing_cpf" id="billing_cpf" value="<?php echo esc_attr($cpf); ?>" placeholder="Digite aqui seu CPF *" required />
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const cpfField = document.querySelector('#billing_cpf');
        if (!cpfField) return;

        cpfField.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    });
    </script>
    <?php
}

}
