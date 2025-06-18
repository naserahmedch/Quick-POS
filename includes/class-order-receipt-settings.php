<?php
if (! defined('ABSPATH')) exit;

class Order_Receipt_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        // Shop Name settings
        register_setting('worp_settings_group', 'worp_shop_name');
        register_setting('worp_settings_group', 'worp_shop_name_size');
        register_setting('worp_settings_group', 'worp_shop_name_weight');
        register_setting('worp_settings_group', 'worp_shop_name_visible');

        // Customer Name settings
        register_setting('worp_settings_group', 'worp_customer_name_size');
        register_setting('worp_settings_group', 'worp_customer_name_weight');
        register_setting('worp_settings_group', 'worp_customer_name_visible');

        // Address settings
        register_setting('worp_settings_group', 'worp_address_size');
        register_setting('worp_settings_group', 'worp_address_weight');
        register_setting('worp_settings_group', 'worp_address_visible');

        // Phone settings
        register_setting('worp_settings_group', 'worp_phone_size');
        register_setting('worp_settings_group', 'worp_phone_weight');
        register_setting('worp_settings_group', 'worp_phone_visible');

        // Note settings
        register_setting('worp_settings_group', 'worp_note_size');
        register_setting('worp_settings_group', 'worp_note_weight');
        register_setting('worp_settings_group', 'worp_note_visible');
    }

    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1 style="font-size: 24px; font-weight: 600; margin-bottom: 24px;">Receipt Appearance Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields('worp_settings_group'); ?>

                <!-- SHOP NAME -->
                <div class="worp-section">
                    <div class="worp-section-title">Shop Name</div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_shop_name">Shop Name</label>
                            <input type="text" id="worp_shop_name" name="worp_shop_name"
                                value="<?php echo esc_attr(get_option('worp_shop_name', '')); ?>" />
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_shop_name_size">Shop Name Font Size</label>
                            <input type="number" id="worp_shop_name_size" name="worp_shop_name_size"
                                value="<?php echo esc_attr(get_option('worp_shop_name_size', '16')); ?>" min="8" max="48" />
                            <span>px</span>
                        </div>
                        <p class="description">Recommended size: 12–20 px</p>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_shop_name_weight">Shop Name Font Weight</label>
                            <select id="worp_shop_name_weight" name="worp_shop_name_weight">
                                <option value="normal" <?php selected(get_option('worp_shop_name_weight'), 'normal'); ?>>Normal</option>
                                <option value="bold" <?php selected(get_option('worp_shop_name_weight'), 'bold'); ?>>Bold</option>
                            </select>
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_shop_name_visible">Show Shop Name on Receipt</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="worp_shop_name_visible" name="worp_shop_name_visible" value="1"
                                    <?php checked(get_option('worp_shop_name_visible', true), true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- CUSTOMER -->
                <div class="worp-section">
                    <div class="worp-section-title">Customer Name</div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_customer_name_size">Font Size</label>
                            <input type="number" id="worp_customer_name_size" name="worp_customer_name_size"
                                value="<?php echo esc_attr(get_option('worp_customer_name_size', '14')); ?>" min="8" max="48" />
                            <span>px</span>
                        </div>
                        <p class="description">Recommended size: 12–20 px</p>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_customer_name_weight">Font Weight</label>
                            <select id="worp_customer_name_weight" name="worp_customer_name_weight">
                                <option value="normal" <?php selected(get_option('worp_customer_name_weight'), 'normal'); ?>>Normal</option>
                                <option value="bold" <?php selected(get_option('worp_customer_name_weight'), 'bold'); ?>>Bold</option>
                            </select>
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_customer_name_visible">Show on Receipt</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="worp_customer_name_visible" name="worp_customer_name_visible" value="1"
                                    <?php checked(get_option('worp_customer_name_visible', true), true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ADDRESS -->
                <div class="worp-section">
                    <div class="worp-section-title">Address</div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_address_size">Font Size</label>
                            <input type="number" id="worp_address_size" name="worp_address_size"
                                value="<?php echo esc_attr(get_option('worp_address_size', '14')); ?>" min="8" max="48" />
                            <span>px</span>
                        </div>
                        <p class="description">Recommended size: 12–20 px</p>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_address_weight">Font Weight</label>
                            <select id="worp_address_weight" name="worp_address_weight">
                                <option value="normal" <?php selected(get_option('worp_address_weight'), 'normal'); ?>>Normal</option>
                                <option value="bold" <?php selected(get_option('worp_address_weight'), 'bold'); ?>>Bold</option>
                            </select>
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_address_visible">Show on Receipt</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="worp_address_visible" name="worp_address_visible" value="1"
                                    <?php checked(get_option('worp_address_visible', true), true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- PHONE -->
                <div class="worp-section">
                    <div class="worp-section-title">Phone</div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_phone_size">Font Size</label>
                            <input type="number" id="worp_phone_size" name="worp_phone_size"
                                value="<?php echo esc_attr(get_option('worp_phone_size', '14')); ?>" min="8" max="48" />
                            <span>px</span>
                        </div>
                        <p class="description">Recommended size: 12–20 px</p>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_phone_weight">Font Weight</label>
                            <select id="worp_phone_weight" name="worp_phone_weight">
                                <option value="normal" <?php selected(get_option('worp_phone_weight'), 'normal'); ?>>Normal</option>
                                <option value="bold" <?php selected(get_option('worp_phone_weight'), 'bold'); ?>>Bold</option>
                            </select>
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_phone_visible">Show on Receipt</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="worp_phone_visible" name="worp_phone_visible" value="1"
                                    <?php checked(get_option('worp_phone_visible', true), true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- NOTE -->
                <div class="worp-section">
                    <div class="worp-section-title">Note</div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_note_size">Font Size</label>
                            <input type="number" id="worp_note_size" name="worp_note_size"
                                value="<?php echo esc_attr(get_option('worp_note_size', '14')); ?>" min="8" max="48" />
                            <span>px</span>
                        </div>
                        <p class="description">Recommended size: 12–20 px</p>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_note_weight">Font Weight</label>
                            <select id="worp_note_weight" name="worp_note_weight">
                                <option value="normal" <?php selected(get_option('worp_note_weight'), 'normal'); ?>>Normal</option>
                                <option value="bold" <?php selected(get_option('worp_note_weight'), 'bold'); ?>>Bold</option>
                            </select>
                        </div>
                    </div>

                    <div class="worp-field">
                        <div class="worp-inline-group">
                            <label for="worp_note_visible">Show on Receipt</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="worp_note_visible" name="worp_note_visible" value="1"
                                    <?php checked(get_option('worp_note_visible', true), true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
<?php
    }
}
