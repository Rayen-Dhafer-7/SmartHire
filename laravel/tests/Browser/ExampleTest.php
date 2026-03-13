<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     */
    public function test_basic_example(): void
    {
        $this->browse(function (Browser $browser) {
    $browser->visit('http://smarthire_vue:5174/')
        ->pause(2000);

    $source = $browser->driver->getPageSource();

    $this->assertTrue(
        str_contains($source, 'SmartHire') || str_contains($source, 'Welcome')
    );
});
    }
}
