<?php
/**
 * Generic SMM API Library (Standard v2)
 * Compatible with Baostar, Autosub, and other SMM Panels.
 */
class SMMApi {
    private $apiKey;
    private $apiUrl;

    public function __construct($apiKey, $apiUrl) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Add a new order
     * @param int $service Service ID
     * @param string $link Link or ID 
     * @param int $quantity Quantity
     * @param array $extraParams List of additional parameters (reaction, comments, etc)
     */
    public function addOrder($service, $link, $quantity, $extraParams = []) {
        $params = [
            'key' => $this->apiKey,
            'action' => 'add',
            'service' => $service,
            'link' => $link,
            'quantity' => $quantity
        ];

        if (!empty($extraParams)) {
            $params = array_merge($params, $extraParams);
        }

        return $this->request($params);
    }

    /**
     * Get order status
     * @param int $orderID
     */
    public function getStatus($orderID) {
        return $this->request([
            'key' => $this->apiKey,
            'action' => 'status',
            'order' => $orderID
        ]);
    }

    /**
     * Get account balance
     */
    public function getBalance() {
        return $this->request([
            'key' => $this->apiKey,
            'action' => 'balance'
        ]);
    }

    /**
     * Get service list
     */
    public function getServices() {
        return $this->request([
            'key' => $this->apiKey,
            'action' => 'services'
        ]);
    }

    private function request($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'Connection Error: ' . $error];
        }

        return json_decode($response, true);
    }
}
?>
