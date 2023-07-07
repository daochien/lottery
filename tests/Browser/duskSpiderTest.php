<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class duskSpiderTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function testExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://xosodaiphat.com/xsmb-06-07-2023.html')
                    ->assertSee('Laravel');
            $html = $browser->driver->getPageSource();
            dd($html);
        });
    }
}
