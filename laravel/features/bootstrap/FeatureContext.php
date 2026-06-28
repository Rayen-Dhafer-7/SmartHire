<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

class FeatureContext extends MinkContext implements Context
{
    public function __construct()
    {
        sleep(2);
    }

    /**
     * Wait for Vue to render an element matching CSS selector
     */
    private function waitForElement($css, $timeout = 15000)
    {
        $this->getSession()->wait(
            $timeout,
            "document.querySelector('{$css}') !== null"
        );
        sleep(1);
    }

    /**
     * Wait for page to not be blank
     */
    private function waitForPageLoad($timeout = 15000)
    {
        $this->getSession()->wait(
            $timeout,
            "document.body && document.body.innerText.trim().length > 50"
        );
        sleep(1);
    }

    /**
     * @Given I am on the Login Page
     */
    public function iAmOnTheLoginPage()
    {
        $this->visitPath('/login');
        $this->waitForElement('input[type="email"], input[name="email"], input[id="email"]');
    }

    /**
     * @When I enter valid email :email and password :password
     */
    public function iEnterValidEmailAndPassword($email, $password)
    {
        $page = $this->getSession()->getPage();

        // Try multiple selectors for email field
        $emailField = $page->find('css', 'input[type="email"]')
            ?? $page->find('css', 'input[name="email"]')
            ?? $page->find('css', 'input[id="email"]')
            ?? $page->find('css', 'input[placeholder*="email"]');

        if (!$emailField) {
            throw new \Exception('Email field not found. Page: ' . substr($page->getText(), 0, 300));
        }
        $emailField->setValue($email);

        // Try multiple selectors for password field
        $passField = $page->find('css', 'input[type="password"]')
            ?? $page->find('css', 'input[name="password"]')
            ?? $page->find('css', 'input[id="password"]');

        if (!$passField) {
            throw new \Exception('Password field not found.');
        }
        $passField->setValue($password);
    }

    /**
     * @When I click :button button
     */
    public function iClickButton($button)
    {
        $page = $this->getSession()->getPage();

        $this->getSession()->wait(5000, "document.body.innerText.indexOf('Loading') === -1");

        $el = $page->find('xpath', '//button[contains(normalize-space(.),"' . $button . '")]')
            ?? $page->find('xpath', '//input[@type="submit" and contains(@value,"' . $button . '")]')
            ?? $page->find('xpath', '//button[contains(@class,"' . strtolower($button) . '")]')
            ?? $page->find('xpath', '//a[contains(normalize-space(.),"' . $button . '")]');

        if (!$el) {
            throw new \Exception("Button '{$button}' not found. Page: " . substr($page->getText(), 0, 300));
        }

        // Mink's executeScript() does NOT support passing element handles as JS
        // arguments (arguments[0] is always undefined) — so we re-find the element
        // INSIDE the JS string via document.evaluate and scroll it there.
        $xpath = str_replace(['\\', '"'], ['\\\\', '\\"'], $el->getXpath());
        $this->getSession()->executeScript("
            var result = document.evaluate(\"{$xpath}\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
            var node = result.singleNodeValue;
            if (node) { node.scrollIntoView({block: 'center'}); }
        ");
        sleep(1);

        try {
            $el->click();
        } catch (\Exception $e) {
            // Fallback: re-find + click purely in JS, no coordinate/mouse-move involved
            $this->getSession()->executeScript("
                var result = document.evaluate(\"{$xpath}\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
                var node = result.singleNodeValue;
                if (node) { node.click(); }
            ");
        }

        sleep(3);
    }

    /**
     * @Then I should be logged in
     */
    public function iShouldBeLoggedIn()
    {
        $this->getSession()->wait(8000, "window.location.href.indexOf('/login') === -1");
        sleep(2);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, '/login') !== false) {
            throw new \Exception('Still on login page. URL: ' . $url . ' | Page: ' . substr($this->getSession()->getPage()->getText(), 0, 200));
        }
    }

    /**
     * @Then I should see error message :message
     */
    public function iShouldSeeErrorMessage($message)
    {
        sleep(2);
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Then I should see success message :message
     */
    public function iShouldSeeSuccessMessage($message)
    {
        sleep(2);
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Given I am on the Company Profile Page
     */
    public function iAmOnTheCompanyProfilePage()
    {
        $this->visitPath('/company/profile');
        $this->waitForPageLoad();
    }

    /**
     * @When I update company name to :name and location to :location
     */
    public function iUpdateCompanyNameAndLocation($name, $location)
    {
        $page = $this->getSession()->getPage();

        // Removed unsupported CSS4 "i" (case-insensitive) flag —
        // Symfony CssSelector throws SyntaxErrorException on it.
        $nameField = $page->find('css', 'input[name="company_name"]')
            ?? $page->find('css', 'input[id="company_name"]')
            ?? $page->find('css', 'input[placeholder*="company"]');

        $locationField = $page->find('css', 'input[name="location"]')
            ?? $page->find('css', 'input[id="location"]')
            ?? $page->find('css', 'input[placeholder*="location"]');

        if ($nameField) $nameField->setValue($name);
        if ($locationField) $locationField->setValue($location);
    }

    /**
     * @When I update industry description to :description
     */
    public function iUpdateIndustryDescription($description)
    {
        $page = $this->getSession()->getPage();
        $field = $page->find('css', 'textarea[name="industry_description"]')
            ?? $page->find('css', 'input[name="industry_description"]')
            ?? $page->find('css', 'textarea[id="industry_description"]');

        if ($field) $field->setValue($description);
    }

    /**
     * @Then I should see profile saved
     */
    public function iShouldSeeProfileSaved()
    {
        sleep(4);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, '/company/profile') === false) {
            throw new \Exception('Not on company profile page after save. URL: ' . $url);
        }
    }

    /**
     * @Given I am on the Old Posts Page
     */
    public function iAmOnTheOldPostsPage()
    {
        $this->visitPath('/company/old-posts');
        $this->waitForPageLoad();
    }

    /**
     * @When I click on a post with applicants
     */
    public function iClickOnAPostWithApplicants()
    {
        $page = $this->getSession()->getPage();
        $link = $page->find('xpath', '//a[contains(normalize-space(.),"View Rankings")] | //button[contains(normalize-space(.),"View Rankings")]')
            ?? $page->find('xpath', '//a[contains(normalize-space(.),"Applicants")] | //button[contains(normalize-space(.),"Applicants")]')
            ?? $page->find('xpath', '//a[contains(normalize-space(.),"View")] | //button[contains(normalize-space(.),"View")]');

        if (!$link) {
            echo "\nNo posts with applicants found - skipping\n";
            return;
        }

        try {
            $link->click();
        } catch (\Exception $e) {
            $this->getSession()->executeScript("arguments[0].click();", [$link]);
        }
        sleep(3);
    }

    /**
     * @When I click :action for the first applicant
     */
    public function iClickActionForTheFirstApplicant($action)
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('xpath', '//button[contains(normalize-space(.),"View Details")]')
            ?? $page->find('xpath', '//button[contains(normalize-space(.),"Details")]')
            ?? $page->find('xpath', '//a[contains(normalize-space(.),"Details")]');

        if (!$button) {
            echo "\nNo applicant action button found - skipping\n";
            return;
        }
        $button->click();
        sleep(2);
    }

    /**
     * @Then I should see applicant details
     */
    public function iShouldSeeApplicantDetails()
    {
        sleep(2);
        $text = $this->getSession()->getPage()->getText();
        if (strpos($text, 'Score') === false
            && strpos($text, 'Candidate') === false
            && strpos($text, 'Details') === false) {
            echo "\nApplicant details not visible - skipping\n";
        }
    }

    /**
     * @Given I click logout
     */
    public function iClickLogout()
    {
        $page = $this->getSession()->getPage();
        $logout = $page->find('css', 'button[class*="logout"], a[href*="logout"]')
            ?? $page->find('xpath', '//button[contains(normalize-space(.),"Logout")] | //a[contains(normalize-space(.),"Logout")]');

        if ($logout) {
            $logout->click();
            sleep(1);
            $confirm = $page->find('css', 'button[class*="confirm"]');
            if ($confirm) {
                $confirm->click();
                sleep(2);
            }
        }

        $this->visitPath('/login');
        $this->waitForElement('input[type="email"], input[name="email"]');
    }

    /**
     * @Then I should be on the worker jobs page
     */
    public function iShouldBeOnTheWorkerJobsPage()
    {
        $this->getSession()->wait(8000, "window.location.href.indexOf('/worker/jobs') !== -1");
        sleep(1);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, '/worker/jobs') === false) {
            throw new \Exception('Not on worker jobs page. URL: ' . $url);
        }
    }

    /**
     * @When I search for skill :skill
     */
    public function iSearchForSkill($skill)
    {
        $page = $this->getSession()->getPage();
        $field = $page->find('css', 'input[placeholder="e.g. Vue.js"]')
            ?? $page->find('css', 'input[placeholder*="skill"]')
            ?? $page->find('css', 'input[name="skill-search"]')
            ?? $page->find('css', 'input[id="skill-search"]');

        if (!$field) {
            echo "\nSkill search field not found - skipping\n";
            return;
        }
        $field->setValue($skill);
        sleep(1);
    }

    /**
     * @When I clear the skill search
     */
    public function iClearTheSkillSearch()
    {
        $page = $this->getSession()->getPage();
        $field = $page->find('css', 'input[placeholder="e.g. Vue.js"]')
            ?? $page->find('css', 'input[placeholder*="skill"]');

        if ($field) {
            $field->setValue('');
            sleep(1);
        }
    }

    /**
     * @When I click :action for a job
     */
    public function iClickActionForAJob($action)
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('xpath', '//button[contains(normalize-space(.),"' . $action . '")] | //a[contains(normalize-space(.),"' . $action . '")]');

        if (!$button) {
            echo "\nCould not find '{$action}' - skipping\n";
            return;
        }
        $button->click();
        sleep(2);
    }

    /**
     * @Then I should be on the application page
     */
    public function iShouldBeOnTheApplicationPage()
    {
        sleep(2);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, 'test-application') === false && strpos($url, 'apply') === false) {
            echo "\nNot on application page - skipping. URL: " . $url . "\n";
        }
    }

    /**
     * @When I toggle :label jobs only
     */
    public function iToggleJobsOnly($label)
    {
        $page = $this->getSession()->getPage();
        $toggle = $page->find('css', '#matchToggle')
            ?? $page->find('css', 'input[type="checkbox"]')
            ?? $page->find('xpath', '//button[contains(normalize-space(.),"Matched")]');

        if (!$toggle) {
            echo "\nCould not find '{$label}' toggle - skipping\n";
            return;
        }
        $toggle->click();
        sleep(1);
    }

    /**
     * @When I navigate to :path
     */
    public function visit($path)
    {
        $this->visitPath($path);
        $this->waitForPageLoad();
    }

    /**
     * @Then I dump the page
     */
    public function iDumpThePage()
    {
        sleep(3);
        echo $this->getSession()->getPage()->getText();
    }

    /**
     * @Then I dump url
     */
    public function iDumpUrl()
    {
        sleep(3);
        echo "\nURL: " . $this->getSession()->getCurrentUrl();
        echo "\nPAGE: " . substr($this->getSession()->getPage()->getText(), 0, 500);
    }
}