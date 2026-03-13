<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    #[BeforeClass]
    public static function prepare(): void
    {
        // Using Selenium container - no local chromedriver needed
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--disable-gpu',
            '--headless=new',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
    protected function baseUrl(): string
    {
        return 'http://smarthire_vue:5174';
    }
}