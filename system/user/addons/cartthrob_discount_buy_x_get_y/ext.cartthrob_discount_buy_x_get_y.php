<?php

class Cartthrob_discount_buy_x_get_y_ext
{
    public $name = CARTTHROB_BUY_X_GET_Y_NAME;
    public $version = CARTTHROB_BUY_X_GET_Y_VERSION;
    public $description = CARTTHROB_BUY_X_GET_Y_DESC;
    public $settings_exist = 'n';
    public $docs_url = '';

    public $settings = [];

    /**
     * Cartthrob_shipping_fedex_ext constructor.
     * @param string $settings
     */
    public function __construct($settings = '')
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [];
    }

    /**
     * Register the shipping plugin with the plugin service
     */
    public function cartthrob_boot()
    {
        ee()->lang->load('cartthrob_discount_buy_x_get_y', $idiom = '', $return = false, $add_suffix = true, $alt_path = __DIR__ . '/');
        ee('cartthrob:PluginService')->register(Cartthrob_discount_buy_x_get_y::class);
    }

    public function activate_extension()
    {
        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'method' => 'cartthrob_boot',
            'hook' => 'cartthrob_boot',
            'settings' => serialize($this->settings),
            'priority' => 10,
            'version' => $this->version,
            'enabled' => 'y',
        ]);
    }

    /**
     * @param string $current
     * @return bool
     */
    public function update_extension($current = '')
    {
        if ($current == '' or $current == $this->version) {
            return false;
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update('extensions', ['version' => $this->version]);
    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }
}
