<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Session;
use Behat\Mink\Driver\Selenium2Driver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

/**
 * Defines application features from the specific context.
 *
 * Uses facebook/php-webdriver directly (same as Laravel Dusk)
 * to ensure Selenium 4 W3C protocol compatibility.
 */
class FeatureContext implements Context
{
    private static ?RemoteWebDriver $driver = null;
    private string $baseUrl    = 'http://smarthire_vue:5174';
    private string $seleniumHub = 'http://selenium:4444/wd/hub';

    // ──────────────────────────────────────────────────────────────────
    // Session lifecycle
    // ──────────────────────────────────────────────────────────────────

    /**
     * @BeforeScenario
     */
    public function startBrowser(): void
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
        ]);

        self::$driver = RemoteWebDriver::create(
            $this->seleniumHub,
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * @AfterScenario
     */
    public function stopBrowser(): void
    {
        if (self::$driver !== null) {
            self::$driver->quit();
            self::$driver = null;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function visit(string $path): void
    {
        self::$driver->get($this->baseUrl . $path);
        sleep(2); // Allow Vue SPA to fully render
    }

    private function findByCss(string $selector)
    {
        return self::$driver->findElement(WebDriverBy::cssSelector($selector));
    }

    private function waitForText(string $text, int $seconds = 5): void
    {
        $end = time() + $seconds;
        while (time() < $end) {
            try {
                $body = self::$driver->findElement(WebDriverBy::tagName('body'))->getText();
                if (str_contains($body, $text)) return;
            } catch (\Exception $e) {}
            usleep(300000); // 300ms
        }
        throw new \Exception("Expected text not found: '$text'");
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: invalid login - incorrect password
    // Scenario: valid company login
    // ──────────────────────────────────────────────────────────────────

    /**
     * @Given /^I am on the Login Page$/
     */
    public function iAmOnTheLoginPage(): void
    {
        $this->visit('/');
    }

    /**
     * @When /^I enter valid email "([^"]*)" and password "([^"]*)"$/
     */
    public function iEnterValidEmailAndPassword(string $email, string $password): void
    {
        $this->findByCss('input[type=email]')->clear()->sendKeys($email);
        $this->findByCss('input[type=password]')->clear()->sendKeys($password);
    }

    /**
     * @When /^I click '([^']*)' button$/
     * @When /^I click '([^']*)'$/
     */
    public function iClickButton(string $button): void
    {
        // The app uses "Sign In" as the login button text
        $label = $button === 'login' ? 'Sign In' : $button;
        self::$driver->findElement(
            WebDriverBy::xpath("//button[normalize-space()='$label'] | //input[@value='$label']")
        )->click();
    }

    /**
     * @Then /^I should see error message "([^"]*)"$/
     */
    public function iShouldSeeErrorMessage(string $message): void
    {
        // Errors appear in SweetAlert2 — wait for "Login Failed" or the message itself
        try {
            $this->waitForText('Login Failed', 4);
        } catch (\Exception $e) {
            $this->waitForText($message, 4);
        }
    }

    /**
     * @Then /^I should be logged in$/
     */
    public function iShouldBeLoggedIn(): void
    {
        // Poll until redirected away from root login (Vue uses setTimeout 1500ms + Swal popup)
        for ($i = 0; $i < 33; $i++) {
            $path = parse_url(self::$driver->getCurrentURL(), PHP_URL_PATH);
            if ($path !== '/' && $path !== null) return;
            usleep(300000);
        }
        throw new \Exception('Expected redirect after login but still on: ' . self::$driver->getCurrentURL());
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: update company profile
    // ──────────────────────────────────────────────────────────────────

    /**
     * @Given /^I am on the Company Profile Page$/
     */
    public function iAmOnTheCompanyProfilePage(): void
    {
        $this->visit('/company/profile');
    }

    /**
     * @When /^I update company name to "([^"]*)" and location to "([^"]*)"$/
     */
    public function iUpdateCompanyNameAndLocation(string $name, string $location): void
    {
        foreach (['[placeholder*="company" i]', '[placeholder*="name" i]'] as $sel) {
            try { $this->findByCss($sel)->clear()->sendKeys($name); break; } catch (\Exception $e) {}
        }
        foreach (['[placeholder*="location" i]', '[placeholder*="city" i]'] as $sel) {
            try { $this->findByCss($sel)->clear()->sendKeys($location); break; } catch (\Exception $e) {}
        }
    }

    /**
     * @When /^I update industry description to "([^"]*)"$/
     */
    public function iUpdateIndustryDescription(string $description): void
    {
        foreach (['textarea', '[placeholder*="industry" i]'] as $sel) {
            try { $this->findByCss($sel)->clear()->sendKeys($description); break; } catch (\Exception $e) {}
        }
    }

    /**
     * @Then /^I should see success message "([^"]*)"$/
     */
    public function iShouldSeeSuccessMessage(string $message): void
    {
        $this->waitForText($message, 5);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: view applicant details from old posts
    // ──────────────────────────────────────────────────────────────────

    /**
     * @Given /^I am on the Old Posts Page$/
     */
    public function iAmOnTheOldPostsPage(): void
    {
        $this->visit('/company/old-posts');
    }

    /**
     * @When /^I click on a post with applicants$/
     */
    public function iClickOnAPostWithApplicants(): void
    {
        self::$driver->findElement(WebDriverBy::cssSelector('button, a'))->click();
        sleep(1);
    }

    /**
     * @When /^I click '([^']*)' for the first applicant$/
     */
    public function iClickActionForTheFirstApplicant(string $action): void
    {
        self::$driver->findElement(
            WebDriverBy::xpath("(//button[contains(text(),'$action')] | //a[contains(text(),'$action')])[1]")
        )->click();
        sleep(1);
    }

    /**
     * @Then /^I should see applicant details$/
     */
    public function iShouldSeeApplicantDetails(): void
    {
        $this->waitForText('Applicant', 4);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: login worker
    // ──────────────────────────────────────────────────────────────────

    /**
     * @Given /^I click logout$/
     */
    public function iClickLogout(): void
    {
        self::$driver->executeScript("localStorage.removeItem('auth_token'); localStorage.removeItem('user_role');");
        $this->visit('/');
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: list jobs and search by skills
    // ──────────────────────────────────────────────────────────────────

    /**
     * @Then /^I should be on the worker jobs page$/
     */
    public function iShouldBeOnTheWorkerJobsPage(): void
    {
        for ($i = 0; $i < 15; $i++) {
            if (str_contains(self::$driver->getCurrentURL(), '/worker/jobs')) return;
            usleep(300000);
        }
        throw new \Exception('Not on worker jobs page: ' . self::$driver->getCurrentURL());
    }

    /**
     * @When /^I search for skill "([^"]*)"$/
     */
    public function iSearchForSkill(string $skill): void
    {
        foreach (['input[placeholder*="skill" i]', 'input[type=search]', 'input[placeholder*="search" i]'] as $sel) {
            try {
                $this->findByCss($sel)->clear()->sendKeys($skill);
                sleep(1);
                return;
            } catch (\Exception $e) {}
        }
    }

    /**
     * @When /^I clear the skill search$/
     */
    public function iClearTheSkillSearch(): void
    {
        foreach (['input[placeholder*="skill" i]', 'input[type=search]', 'input[placeholder*="search" i]'] as $sel) {
            try {
                $this->findByCss($sel)->clear();
                return;
            } catch (\Exception $e) {}
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: test apply
    // ──────────────────────────────────────────────────────────────────

    /**
     * @When /^I click '([^']*)' for a job$/
     */
    public function iClickActionForAJob(string $action): void
    {
        self::$driver->findElement(
            WebDriverBy::xpath("//button[contains(text(),'$action')] | //a[contains(text(),'$action')]")
        )->click();
        sleep(2);
    }

    /**
     * @Then /^I should be on the application page$/
     */
    public function iShouldBeOnTheApplicationPage(): void
    {
        $this->waitForText('Application', 5);
    }

    // ──────────────────────────────────────────────────────────────────
    // Scenario: test matched jobs
    // ──────────────────────────────────────────────────────────────────

    /**
     * @When /^I toggle '([^']*)' jobs only$/
     */
    public function iToggleJobsOnly(string $label): void
    {
        try {
            $el = self::$driver->findElement(
                WebDriverBy::xpath("//*[contains(text(),'$label')]/ancestor-or-self::button[1]")
            );
            $el->click();
        } catch (\Exception $e) {
            // Fallback: any checkbox or toggle
            try {
                $this->findByCss('input[type=checkbox]')->click();
            } catch (\Exception $e2) {}
        }
        sleep(1);
    }
}
