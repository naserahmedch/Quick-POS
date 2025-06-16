<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Order_Receipt_Settings {

    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Render settings page HTML
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Receipt Print Settings', 'woocommerce-order-receipt-print' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'worp_settings_group' );
                    do_settings_sections( 'worp_settings' );
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register plugin settings, sections, and fields
     */
    public function register_settings() {
        register_setting( 'worp_settings_group', 'worp_settings' );

        add_settings_section(
            'worp_main_section',
            __( 'Main Settings', 'woocommerce-order-receipt-print' ),
            null,
            'worp_settings'
        );

        add_settings_field(
            'shop_name',
            __( 'Shop Name', 'woocommerce-order-receipt-print' ),
            array( $this, 'shop_name_field' ),
            'worp_settings',
            'worp_main_section'
        );

        add_settings_field(
            'font_size',
            __( 'Phone Font Size', 'woocommerce-order-receipt-print' ),
            array( $this, 'font_size_field' ),
            'worp_settings',
            'worp_main_section'
        );

        add_settings_field(
            'phone_weight',
            __( 'Phone Font Weight', 'woocommerce-order-receipt-print' ),
            array( $this, 'phone_weight_field' ),
            'worp_settings',
            'worp_main_section'
        );
    }

    /**
     * Individual Field Renderers
     */
    public function shop_name_field() {
        $options = get_option( 'worp_settings' );
        echo '<input type="text" name="worp_settings[shop_name]" value="' . esc_attr( $options['shop_name'] ?? '' ) . '" />';
    }

    public function font_size_field() {
        $options = get_option( 'worp_settings' );
        echo '<input type="number" name="worp_settings[font_size]" value="' . esc_attr( $options['font_size'] ?? 12 ) . '" />';
    }

    public function phone_weight_field() {
        $options = get_option( 'worp_settings' );
        ?>
        <select name="worp_settings[phone_weight]">
            <option value="normal" <?php selected( $options['phone_weight'] ?? '', 'normal' ); ?>>Normal</option>
            <option value="bold" <?php selected( $options['phone_weight'] ?? '', 'bold' ); ?>>Bold</option>
        </select>
        <?php
    }
}
