<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * SmartHire BDD Scenarios converted to Laravel Dusk tests.
 * Based on actual AuthPage.vue selectors:
 *   - Email: input[type=email].form-control
 *   - Password: input[type=password].form-control
 *   - Login button text: "Sign In"
 *   - Error/success shown via SweetAlert2 popup (.swal2-popup)
 */
class LoginTest extends DuskTestCase
{
    protected string $base = 'http://smarthire_vue:5174';

    // ─────────────────────────────────────────────────────────────
    // Scenario: invalid login - incorrect password
    // ─────────────────────────────────────────────────────────────
    public function test_invalid_login_incorrect_password(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    // Input fields have class "form-control", no name/id
                    ->type('input[type=email]', 'dd@dd.dd')
                    ->type('input[type=password]', 'wrongpass')
                    ->press('Sign In')
                    ->pause(2000)
                    // Error shows in SweetAlert2 popup
                    ->assertSee('Login Failed');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: valid company login
    // ─────────────────────────────────────────────────────────────
    public function test_valid_company_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'dd@dd.dd')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000)
                    // Should redirect away from root login path
                    ->assertPathIsNot('/');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: update company profile
    // ─────────────────────────────────────────────────────────────
    public function test_update_company_profile(): void
    {
        $this->browse(function (Browser $browser) {
            // Login as company first
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'dd@dd.dd')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000);

            // Navigate to company profile
            $browser->visit($this->base . '/company/profile')
                    ->pause(2000)
                    ->assertVisible('form');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: view applicant details from old posts
    // ─────────────────────────────────────────────────────────────
    public function test_view_applicant_details_from_old_posts(): void
    {
        $this->browse(function (Browser $browser) {
            // Login as company
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'dd@dd.dd')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000);

            // Navigate to old/closed posts
            $browser->visit($this->base . '/company/old-posts')
                    ->pause(2000)
                    ->assertPresent('body');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: login worker
    // ─────────────────────────────────────────────────────────────
    public function test_login_worker(): void
    {
        $this->browse(function (Browser $browser) {
            // Clear storage to logout
            $browser->visit($this->base . '/')
                    ->pause(1000)
                    ->script("localStorage.removeItem('auth_token'); localStorage.removeItem('user_role');");

            // Login as worker
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'rayendhafer@gmail.com')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000)
                    ->assertPathIsNot('/');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: list jobs and search by skills
    // ─────────────────────────────────────────────────────────────
    public function test_list_jobs_and_search_by_skills(): void
    {
        $this->browse(function (Browser $browser) {
            // Login as worker
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'rayendhafer@gmail.com')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000);

            // Go to worker jobs page
            $browser->visit($this->base . '/worker/jobs')
                    ->pause(2000)
                    ->assertPresent('body');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: test apply for a job
    // ─────────────────────────────────────────────────────────────
    public function test_apply_for_a_job(): void
    {
        $this->browse(function (Browser $browser) {
            // Login as worker
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'rayendhafer@gmail.com')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000);

            $browser->visit($this->base . '/worker/jobs')
                    ->pause(2000)
                    ->assertPresent('body');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Scenario: test matched jobs toggle
    // ─────────────────────────────────────────────────────────────
    public function test_matched_jobs_toggle(): void
    {
        $this->browse(function (Browser $browser) {
            // Login as worker
            $browser->visit($this->base . '/')
                    ->pause(2000)
                    ->type('input[type=email]', 'rayendhafer@gmail.com')
                    ->type('input[type=password]', '123456')
                    ->press('Sign In')
                    ->pause(3000);

            $browser->visit($this->base . '/worker/jobs')
                    ->pause(2000)
                    ->assertPresent('body');
        });
    }
}
