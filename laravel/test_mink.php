<?php
require 'vendor/autoload.php';
use OAndreyev\Mink\Driver\WebDriver;
use Behat\Mink\Session;

// Try both endpoints
foreach (['http://selenium:4444/wd/hub', 'http://selenium:4444'] as $hub) {
    echo "Trying: $hub\n";
    $driver = new WebDriver($hub, [
        'browserName'        => 'chrome',
        'goog:chromeOptions' => [
            'args' => ['--headless=new', '--no-sandbox', '--disable-gpu'],
        ],
    ]);
    $session = new Session($driver);
    try {
        $session->start();
        $session->visit('http://smarthire_vue:5174/');
        echo "URL: " . $session->getCurrentUrl() . "\n";
        $session->stop();
        echo "SUCCESS with $hub\n";
        break;
    } catch (\Exception $e) {
        echo "ERROR with $hub: " . $e->getMessage() . "\n";
    }
}

$driver = new WebDriver('http://selenium:4444/wd/hub', [
    'browserName'        => 'chrome',
    'goog:chromeOptions' => [
        'args' => ['--headless=new', '--no-sandbox', '--disable-gpu'],
    ],
]);

$session = new Session($driver);
try {
    $session->start();
    $session->visit('http://smarthire_vue:5174/');
    echo "URL: " . $session->getCurrentUrl() . "\n";
    $session->stop();
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
