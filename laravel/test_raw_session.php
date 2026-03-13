<?php
require 'vendor/autoload.php';
$client = new \GuzzleHttp\Client();
try {
    $resp = $client->post('http://selenium:4444/wd/hub/session', [
        'json' => [
            'capabilities' => [
                'alwaysMatch' => [
                    'browserName' => 'chrome',
                    'goog:chromeOptions' => [
                        'args' => ['--headless=new', '--no-sandbox', '--disable-gpu'],
                    ],
                ],
            ],
        ],
    ]);
    echo "Status: " . $resp->getStatusCode() . "\n";
    echo substr($resp->getBody(), 0, 200) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
