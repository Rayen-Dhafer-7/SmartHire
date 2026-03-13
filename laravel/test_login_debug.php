<?php
require 'vendor/autoload.php';
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

$options = (new ChromeOptions)->addArguments([
    '--headless=new', '--disable-gpu', '--no-sandbox', '--disable-dev-shm-usage', '--window-size=1920,1080',
]);
$driver = RemoteWebDriver::create(
    'http://selenium:4444/wd/hub',
    DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options)
);

$driver->get('http://smarthire_vue:5174/');
sleep(3);

// Fill login
$driver->findElement(WebDriverBy::cssSelector('input[type=email]'))->clear()->sendKeys('dd@dd.dd');
$driver->findElement(WebDriverBy::cssSelector('input[type=password]'))->clear()->sendKeys('123456');
$driver->findElement(WebDriverBy::xpath("//button[normalize-space()='Sign In']"))->click();

sleep(5);
echo "URL after login: " . $driver->getCurrentURL() . "\n";
echo "Page text: " . substr($driver->findElement(WebDriverBy::tagName('body'))->getText(), 0, 500) . "\n";

$driver->quit();
