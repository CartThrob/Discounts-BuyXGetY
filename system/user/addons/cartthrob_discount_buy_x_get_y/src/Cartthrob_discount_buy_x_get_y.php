<?php if (!defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

use CartThrob\Math\Number;
use CartThrob\Plugins\Discount\DiscountPlugin;
use CartThrob\Plugins\Discount\ValidateCartInterface;

class Cartthrob_discount_buy_x_get_y extends DiscountPlugin implements ValidateCartInterface
{
    public $title = 'buy_x_get_y';

    public $settings = [
        [
            'name' => 'buy_x_entry_ids',
            'short_name' => 'x_entry_ids',
            'note' => 'separate_multiple_entry_ids',
            'type' => 'text'
        ],
        [
            'name' => 'purchase_quantity',
            'short_name' => 'buy_x',
            'note' => 'enter_the_purchase_quantity',
            'type' => 'text'
        ],
        [
            'name' => 'get_y_entry_ids',
            'short_name' => 'y_entry_ids',
            'note' => 'separate_multiple_entry_ids',
            'type' => 'text'
        ],
        [
            'name' => 'discount_quantity',
            'short_name' => 'get_y_free',
            'note' => 'enter_the_number_of_items',
            'type' => 'text'
        ],
        [
            'name' => 'percentage_off',
            'short_name' => 'percentage_off',
            'note' => 'enter_the_percentage_discount',
            'type' => 'text'
        ],
        [
            'name' => 'amount_off',
            'short_name' => 'amount_off',
            'note' => 'enter_the_discount_amount',
            'type' => 'text'
        ],
    ];

    public function get_discount()
    {
        $discount = 0;
        $x_entry_ids = [];
        $x_not_entry_ids = [];
        $y_entry_ids = [];
        $y_in_cart = false;
        $y_found = 0;
        $x_found = 0;

        // CHECK AMOUNTS AND PERCENTAGES
        if ($this->plugin_settings('percentage_off') !== '') {
            $percentage_off = .01 * Number::sanitize($this->plugin_settings('percentage_off'));
            if ($percentage_off > 100) {
                $percentage_off = 100;
            } else if ($percentage_off < 0) {
                $percentage_off = 0;
            }
        } else {
            $amount_off = Number::sanitize($this->plugin_settings('amount_off'));
        }

        if ($this->plugin_settings('y_entry_ids')) {
            $y_entry_ids = preg_split('/\s*[,|]\s*/', $this->plugin_settings('y_entry_ids'));
        }

        if ($y_entry_ids) {
            foreach ($this->core->cart->items() as $item) {
                if (in_array($item->product_id(), $y_entry_ids)) {
                    $y_in_cart = true;
                    $y_found++;
                }
            }
        }

        if ($y_in_cart === false) {
            return $discount;
        }

        // CHECK ENTRY IDS
        if ($this->plugin_settings('x_entry_ids')) {
            if (preg_match('/^not (.*)/', trim($this->plugin_settings('x_entry_ids')), $matches)) {
                $x_not_entry_ids = preg_split('/\s*[,|]\s*/', $matches[1]);
            } else {
                $x_entry_ids = preg_split('/\s*[,|]\s*/', trim($this->plugin_settings('x_entry_ids')));
            }
        }

        //check if we have enough X products to warrant a discount
        $item_limit = ($this->plugin_settings('item_limit')) ? $this->plugin_settings('item_limit') : false;
        $items = [];
        if (count($x_entry_ids) > 0 || count($x_not_entry_ids) > 0) {
            foreach ($this->core->cart->items() as $item) {
                if (count($x_entry_ids) > 0) {
                    if ($item->product_id() && in_array($item->product_id(), $x_entry_ids)) {
                        for ($i = 0; $i < $item->quantity(); $i++) {
                            $items[] = $item->price();
                        }
                    }
                } else {
                    if ($item->product_id() && !in_array($item->product_id(), $x_not_entry_ids)) {
                        for ($i = 0; $i < $item->quantity(); $i++) {
                            $items[] = $item->price();
                        }
                    }
                }
            }

        } else {
            foreach ($this->core->cart->items() as $item) {
                for ($i = 0; $i < $item->quantity(); $i++) {
                    $items[] = $item->price();
                }
            }
        }

        $buy_x = $this->plugin_settings('buy_x');
        $counts = [];
        if ($items < $buy_x) {
            return $discount;
        }

        //now loop through Y products and calculate discount(s)
        $y_items = [];
        if ($y_entry_ids) {
            foreach ($this->core->cart->items() as $item) {
                if (count($y_entry_ids) > 0) {
                    if ($item->product_id() && in_array($item->product_id(), $y_entry_ids)) {
                        for ($i = 0; $i < $item->quantity(); $i++) {
                            if (isset($percentage_off)) {
                                if ($this->plugin_settings('get_y_free') && $this->plugin_settings('get_y_free') > $i) {
                                    $discount += $item->price() * $percentage_off;
                                } elseif (!$this->plugin_settings('get_y_free')) {
                                    $discount += $item->price() * $percentage_off;
                                }
                            } else {
                                if ($this->plugin_settings('get_y_free') && $this->plugin_settings('get_y_free') > $i) {
                                    $discount += $amount_off;
                                } elseif (!$this->plugin_settings('get_y_free')) {
                                    $discount = $amount_off;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $discount;
    }

    public function validateCart(): bool
    {
        $entry_ids = [];
        $not_entry_ids = [];
        if (!$this->plugin_settings('x_entry_ids')) {
            $found = 0;
            foreach ($this->core->cart->items() as $item) {
                if ($item->quantity() >= Number::sanitize($this->plugin_settings('buy_x'))) {
                    return true;
                } else {

                }

                $this->set_error($this->core->lang('coupon_minimum_not_reached'));
                return false;
            }

        }
        if ($this->plugin_settings('x_entry_ids')) {

            $entry_ids = preg_split('/\s*,\s*/', trim($this->plugin_settings('x_entry_ids')));
            if (preg_match('/^not (.*)/', trim($this->plugin_settings('x_entry_ids')), $matches)) {
                $codes = (explode('not', $matches[1], 2));
                $not_entry_ids = preg_split('/\s*,\s*/', $codes[1]);
            }

        }

        if (count($entry_ids)) {
            foreach ($this->core->cart->items() as $item) {
                if ($item->product_id() && in_array($item->product_id(), $entry_ids)) {
                    if ($item->quantity() >= Number::sanitize($this->plugin_settings('buy_x'))) {
                        return true;
                    }
                }
                $this->set_error($this->core->lang('coupon_minimum_not_reached'));
            }

        } elseif (count($not_entry_ids)) {

            foreach ($this->core->cart->items() as $item) {
                if ($item->product_id() && !in_array($item->product_id(), $entry_ids)) {
                    if ($item->quantity() >= Number::sanitize($this->plugin_settings('buy_x'))) {
                        return true;
                    }
                }
                $this->set_error($this->core->lang('coupon_minimum_not_reached'));
            }

        } else {
            $this->set_error($this->core->lang('coupon_not_valid_for_items'));
        }

        return false;
    }
}