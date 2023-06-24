<?php

require_once __DIR__ . '/vendor/autoload.php';

const CARTTHROB_BUY_X_GET_Y_NAME = 'CartThrob Buy X Get Y Discount Plugin';
const CARTTHROB_BUY_X_GET_Y_VERSION = '1.0.0';
const CARTTHROB_BUY_X_GET_Y_DESC = 'Buy X Get Y CartThrob Discount Plugin';

return [
    'author' => 'Foster Made',
    'author_url' => 'https://fostermade.co/',
    'name' => CARTTHROB_BUY_X_GET_Y_NAME,
    'description' => CARTTHROB_BUY_X_GET_Y_DESC,
    'version' => CARTTHROB_BUY_X_GET_Y_VERSION,
    'namespace' => 'CartThrob\BuyXGetYDiscount',
    'settings_exist' => false,
];
