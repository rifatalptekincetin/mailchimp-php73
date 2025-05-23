<?php

require_once __DIR__ . '/vendor/autoload.php';

use MailchimpMarketing\ApiClient;

class MailchimpService
{
    private $client;
    private $listId;

    public function __construct($apiKey, $serverPrefix, $listId)
    {
        $this->listId = $listId;

        $this->client = new ApiClient();
        $this->client->setConfig([
            'apiKey' => $apiKey,
            'server' => $serverPrefix
        ]);
    }

    // Müşteri (abonelik) ekle
    public function addCustomer($email, $firstName = '', $lastName = '')
    {
        return $this->client->lists->addListMember($this->listId, [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $firstName,
                'LNAME' => $lastName
            ]
        ]);
    }

    // Mağaza oluştur
    public function createStore($storeId, $storeName, $email, $domain = 'example.com', $currency = 'USD')
    {
        return $this->client->ecommerce->createStore([
            'id' => $storeId,
            'list_id' => $this->listId,
            'name' => $storeName,
            'email_address' => $email,
            'currency_code' => $currency,
            'domain' => $domain
        ]);
    }

    // Ürün ekle
    public function addProduct($storeId, $productId, $title, $price, $url)
    {
        return $this->client->ecommerce->addProduct($storeId, [
            'id' => $productId,
            'title' => $title,
            'variants' => [[
                'id' => $productId . '-v1',
                'title' => $title,
                'price' => $price
            ]],
            'url' => $url
        ]);
    }

    public function addOrder($storeId, $orderId, $customer, $products, $currency = 'USD', $orderTotal = 0)
    {
        $lineItems = [];

        foreach ($products as $product) {
            $lineItems[] = [
                'id' => $product['id'] . '-line',
                'product_id' => $product['id'],
                'product_variant_id' => $product['id'] . '-v1',
                'quantity' => $product['quantity'],
                'price' => $product['price']
            ];
        }

        $orderData = [
            'id' => $orderId,
            'customer' => [
                'id' => $customer['id'],
                'email_address' => $customer['email'],
                'opt_in_status' => true,
                'first_name' => $customer['first_name'] ?? '',
                'last_name' => $customer['last_name'] ?? ''
            ],
            'currency_code' => $currency,
            'order_total' => $orderTotal,
            'lines' => $lineItems
        ];

        return $this->client->ecommerce->createOrder($storeId, $orderData);
    }
}
