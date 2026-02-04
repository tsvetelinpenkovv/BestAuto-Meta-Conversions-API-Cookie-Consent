<?php
/**
 * BestAuto Meta Conversions API Handler
 * Using a unique class name to avoid conflicts: BestAutoCapiService
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BestAutoCapiService
{
    private $pixel_id;
    private $access_token;
    private $test_code;

    public function __construct()
    {
        $this->pixel_id = Configuration::get('BESTAUTO_CAPI_PIXEL_ID');
        $this->access_token = Configuration::get('BESTAUTO_CAPI_ACCESS_TOKEN');
        $this->test_code = Configuration::get('BESTAUTO_CAPI_TEST_CODE');
    }

    /**
     * Get Client IP with Cloudflare support
     */
    private function getClientIp()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    public function sendEvent($event_name, $event_id, $custom_data = array())
    {
        if (empty($this->pixel_id) || empty($this->access_token)) {
            return false;
        }

        try {
            $context = Context::getContext();
            if (!$context) return false;

            $customer = $context->customer;

            $user_data = array(
                'client_ip_address' => $this->getClientIp(),
                'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'fbp' => isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : null,
                'fbc' => isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : null,
            );

            if (isset($customer) && Validate::isLoadedObject($customer)) {
                $user_data['em'] = hash('sha256', strtolower(trim($customer->email)));
                if (!empty($customer->phone)) {
                    $user_data['ph'] = hash('sha256', trim($customer->phone));
                }
            }

            $event = array(
                'event_name' => $event_name,
                'event_time' => time(),
                'event_id' => $event_id,
                'event_source_url' => $context->link->getPageLink('index', true),
                'action_source' => 'website',
                'user_data' => array_filter($user_data),
                'custom_data' => array_filter($custom_data)
            );

            $payload = array(
                'data' => array($event)
            );

            if (!empty($this->test_code)) {
                $payload['test_event_code'] = $this->test_code;
            }

            return $this->executeRequest($payload);
        } catch (Exception $e) {
            return false;
        }
    }

    private function executeRequest($payload)
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $url = 'https://graph.facebook.com/v18.0/' . $this->pixel_id . '/events?access_token=' . $this->access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // We use PrestaShop's native logging to avoid file permission issues
        if ($http_code !== 200) {
            PrestaShopLogger::addLog("BestAuto CAPI Error: HTTP $http_code - Response: $response", 3);
        }

        return $http_code === 200;
    }
}
