<?php
/**
 * BestAuto Meta Conversions API + Cookie Consent
 *
 * @author    Tsvetelin Penkov
 * @copyright 2026 Tsvetelin Penkov
 * @version   1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BestAutoCapi extends Module
{
    public function __construct()
    {
        $this->name = 'bestautocapi';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Tsvetelin Penkov';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('BestAuto Meta Conversions API + Cookie Consent');
        $this->description = $this->l('Интегрира Meta Conversions API (CAPI) с управление на съгласието за бисквитки.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('actionProductAdd') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionCartSave') &&
            Configuration::updateValue('BESTAUTO_CAPI_ENABLED', 0) &&
            Configuration::updateValue('BESTAUTO_CAPI_PIXEL_ID', '') &&
            Configuration::updateValue('BESTAUTO_CAPI_ACCESS_TOKEN', '') &&
            Configuration::updateValue('BESTAUTO_CAPI_TEST_CODE', '') &&
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_VIEW', 1) &&
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_ADD_TO_CART', 1) &&
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_CHECKOUT', 1) &&
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_PURCHASE', 1);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            Configuration::deleteByName('BESTAUTO_CAPI_ENABLED') &&
            Configuration::deleteByName('BESTAUTO_CAPI_PIXEL_ID') &&
            Configuration::deleteByName('BESTAUTO_CAPI_ACCESS_TOKEN') &&
            Configuration::deleteByName('BESTAUTO_CAPI_TEST_CODE') &&
            Configuration::deleteByName('BESTAUTO_CAPI_TRACK_VIEW') &&
            Configuration::deleteByName('BESTAUTO_CAPI_TRACK_ADD_TO_CART') &&
            Configuration::deleteByName('BESTAUTO_CAPI_TRACK_CHECKOUT') &&
            Configuration::deleteByName('BESTAUTO_CAPI_TRACK_PURCHASE');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitBestAutoCapi')) {
            Configuration::updateValue('BESTAUTO_CAPI_ENABLED', (int)Tools::getValue('BESTAUTO_CAPI_ENABLED'));
            Configuration::updateValue('BESTAUTO_CAPI_PIXEL_ID', Tools::getValue('BESTAUTO_CAPI_PIXEL_ID'));
            Configuration::updateValue('BESTAUTO_CAPI_ACCESS_TOKEN', Tools::getValue('BESTAUTO_CAPI_ACCESS_TOKEN'));
            Configuration::updateValue('BESTAUTO_CAPI_TEST_CODE', Tools::getValue('BESTAUTO_CAPI_TEST_CODE'));
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_VIEW', (int)Tools::getValue('BESTAUTO_CAPI_TRACK_VIEW'));
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_ADD_TO_CART', (int)Tools::getValue('BESTAUTO_CAPI_TRACK_ADD_TO_CART'));
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_CHECKOUT', (int)Tools::getValue('BESTAUTO_CAPI_TRACK_CHECKOUT'));
            Configuration::updateValue('BESTAUTO_CAPI_TRACK_PURCHASE', (int)Tools::getValue('BESTAUTO_CAPI_TRACK_PURCHASE'));

            $output .= $this->displayConfirmation($this->l('Settings updated. Настройките са обновени.'));
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Meta CAPI Settings / Настройки на Meta CAPI'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable CAPI'),
                        'desc' => $this->l('Activate or deactivate the Conversions API tracking. / Активирайте или деактивирайте проследяването чрез Conversions API.'),
                        'name' => 'BESTAUTO_CAPI_ENABLED',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled / Активирано')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled / Деактивирано'))
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pixel ID'),
                        'desc' => $this->l('Enter your Meta Pixel ID. / Въведете вашия Meta Pixel ID.'),
                        'name' => 'BESTAUTO_CAPI_PIXEL_ID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Access Token'),
                        'desc' => $this->l('Your Meta Graph API Access Token. / Вашият Access Token за Meta Graph API.'),
                        'name' => 'BESTAUTO_CAPI_ACCESS_TOKEN',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Test Event Code'),
                        'name' => 'BESTAUTO_CAPI_TEST_CODE',
                        'desc' => $this->l('Used for testing events in Meta Events Manager. Leave empty for production. / Използва се за тестване на събития в Meta Events Manager. Оставете празно за реална работа.')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Track ViewContent'),
                        'desc' => $this->l('Track when a user views a product. / Проследяване при преглед на продукт.'),
                        'name' => 'BESTAUTO_CAPI_TRACK_VIEW',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Track AddToCart'),
                        'desc' => $this->l('Track when a user adds a product to the cart. / Проследяване при добавяне на продукт в количката.'),
                        'name' => 'BESTAUTO_CAPI_TRACK_ADD_TO_CART',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Track InitiateCheckout'),
                        'desc' => $this->l('Track when a user starts the checkout process. / Проследяване при започване на поръчка.'),
                        'name' => 'BESTAUTO_CAPI_TRACK_CHECKOUT',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Track Purchase'),
                        'desc' => $this->l('Track successful purchases. / Проследяване при успешна поръчка.'),
                        'name' => 'BESTAUTO_CAPI_TRACK_PURCHASE',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save / Запази'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBestAutoCapi';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BESTAUTO_CAPI_ENABLED' => Configuration::get('BESTAUTO_CAPI_ENABLED'),
            'BESTAUTO_CAPI_PIXEL_ID' => Configuration::get('BESTAUTO_CAPI_PIXEL_ID'),
            'BESTAUTO_CAPI_ACCESS_TOKEN' => Configuration::get('BESTAUTO_CAPI_ACCESS_TOKEN'),
            'BESTAUTO_CAPI_TEST_CODE' => Configuration::get('BESTAUTO_CAPI_TEST_CODE'),
            'BESTAUTO_CAPI_TRACK_VIEW' => Configuration::get('BESTAUTO_CAPI_TRACK_VIEW'),
            'BESTAUTO_CAPI_TRACK_ADD_TO_CART' => Configuration::get('BESTAUTO_CAPI_TRACK_ADD_TO_CART'),
            'BESTAUTO_CAPI_TRACK_CHECKOUT' => Configuration::get('BESTAUTO_CAPI_TRACK_CHECKOUT'),
            'BESTAUTO_CAPI_TRACK_PURCHASE' => Configuration::get('BESTAUTO_CAPI_TRACK_PURCHASE'),
        );
    }

    private function hasConsent()
    {
        return isset($_COOKIE['cookie_ue']) && $_COOKIE['cookie_ue'] == '1';
    }

    private function getService()
    {
        $service_path = dirname(__FILE__) . '/classes/BestAutoCapiHandler.php';
        if (file_exists($service_path)) {
            require_once($service_path);
            if (class_exists('BestAutoCapiService')) {
                return new BestAutoCapiService();
            }
        }
        return null;
    }

    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('BESTAUTO_CAPI_ENABLED')) {
            return '';
        }

        try {
            if ($this->context->controller instanceof ProductController && Configuration::get('BESTAUTO_CAPI_TRACK_VIEW')) {
                if ($this->hasConsent()) {
                    $product = $this->context->controller->getProduct();
                    $service = $this->getService();
                    if ($service) {
                        $event_id = 'view_' . (isset($product->id) ? $product->id : '0') . '_' . time();
                        $service->sendEvent('ViewContent', $event_id, array(
                            'content_ids' => array((string)$product->id),
                            'content_type' => 'product',
                            'value' => $product->getPrice(),
                            'currency' => $this->context->currency->iso_code
                        ));
                        $this->context->smarty->assign('bestauto_event_id', $event_id);
                    }
                }
            }
        } catch (Exception $e) {}
        
        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    public function hookActionProductAdd($params)
    {
        if (!Configuration::get('BESTAUTO_CAPI_ENABLED') || !Configuration::get('BESTAUTO_CAPI_TRACK_ADD_TO_CART') || !$this->hasConsent()) {
            return;
        }

        try {
            $id_product = (int)$params['id_product'];
            $product = new Product($id_product);
            $service = $this->getService();
            if ($service && Validate::isLoadedObject($product)) {
                $event_id = 'cart_' . $id_product . '_' . time();
                $service->sendEvent('AddToCart', $event_id, array(
                    'content_ids' => array((string)$id_product),
                    'content_type' => 'product',
                    'value' => $product->getPrice(),
                    'currency' => $this->context->currency->iso_code
                ));
            }
        } catch (Exception $e) {}
    }

    public function hookActionCartSave($params)
    {
        if (!Configuration::get('BESTAUTO_CAPI_ENABLED') || !Configuration::get('BESTAUTO_CAPI_TRACK_CHECKOUT') || !$this->hasConsent()) {
            return;
        }

        try {
            if ($this->context->controller instanceof OrderController && Tools::getValue('step') == 1) {
                $cart = $this->context->cart;
                $service = $this->getService();
                if ($service && Validate::isLoadedObject($cart)) {
                    $event_id = 'checkout_' . $cart->id . '_' . time();
                    $products = $cart->getProducts();
                    $ids = array();
                    foreach ($products as $p) {
                        $ids[] = (string)$p['id_product'];
                    }
                    $service->sendEvent('InitiateCheckout', $event_id, array(
                        'content_ids' => $ids,
                        'content_type' => 'product',
                        'value' => $cart->getOrderTotal(),
                        'currency' => $this->context->currency->iso_code
                    ));
                }
            }
        } catch (Exception $e) {}
    }

    public function hookActionValidateOrder($params)
    {
        if (!Configuration::get('BESTAUTO_CAPI_ENABLED') || !Configuration::get('BESTAUTO_CAPI_TRACK_PURCHASE') || !$this->hasConsent()) {
            return;
        }

        try {
            $order = $params['order'];
            $service = $this->getService();
            if ($service && Validate::isLoadedObject($order)) {
                $event_id = 'purchase_' . $order->id;
                $products = $order->getProducts();
                $ids = array();
                foreach ($products as $p) {
                    $ids[] = (string)$p['product_id'];
                }
                $service->sendEvent('Purchase', $event_id, array(
                    'content_ids' => $ids,
                    'content_type' => 'product',
                    'value' => $order->total_paid,
                    'currency' => $this->context->currency->iso_code
                ));
            }
        } catch (Exception $e) {}
    }
}
