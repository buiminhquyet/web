<?php

class BaostarAPI {
    private $api_key;
    private $api_url;

    public function __construct($api_key, $api_url = 'https://api.baostar.pro/api/v2') {
        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    /**
     * Common request function
     */
    private function request($params) {
        $params['key'] = $this->api_key;
        $post = http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['error' => 'Curl error: ' . $err];
        }

        return json_decode($response, true);
    }

    /**
     * Get account balance
     */
    public function getBalance() {
        return $this->request(['action' => 'balance']);
    }

    /**
     * Get list of all services
     */
    public function getServices() {
        return $this->request(['action' => 'services']);
    }

    /**
     * Create a new order
     * @param int $service Service ID
     * @param string $link Link or ID 
     * @param int $quantity Quantity
     */
    public function addOrder($service, $link, $quantity, $runs = null, $interval = null) {
        $params = [
            'action' => 'add',
            'service' => $service,
            'link' => $link,
            'quantity' => $quantity
        ];
        if ($runs) $params['runs'] = $runs;
        if ($interval) $params['interval'] = $interval;

        return $this->request($params);
    }

    /**
     * Check order status
     * @param int $order_id Baostar Order ID
     */
    public function getStatus($order_id) {
        return $this->request([
            'action' => 'status',
            'order' => $order_id
        ]);
    }

    /**
     * Check multiple orders status
     * @param array $order_ids Array of Baostar Order IDs
     */
    public function getMultiStatus($order_ids) {
        return $this->request([
            'action' => 'status',
            'orders' => implode(',', $order_ids)
        ]);
    }
}
