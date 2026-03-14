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
     * @Given I am on the Login Page
     */
    public function iAmOnTheLoginPage()
    {
        $this->visitPath('/login');
        sleep(2);
    }

    /**
     * @When I enter valid email :email and password :password
     */
    public function iEnterValidEmailAndPassword($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);
    }

    /**
     * @When I click :button button
     */
    public function iClickButton($button)
    {
        $this->pressButton($button);
        sleep(3);
    }

    /**
     * @Then I should see error message :message
     */
    public function iShouldSeeErrorMessage($message)
    {
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Then I should be logged in
     */
    public function iShouldBeLoggedIn()
    {
        sleep(3);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, '/login') !== false) {
            throw new \Exception('Still on login page - login failed. URL: ' . $url);
        }
    }

    /**
     * @Given I am on the Company Profile Page
     */
    public function iAmOnTheCompanyProfilePage()
    {
        $this->visitPath('/company/profile');
        sleep(2);
    }

    /**
     * @When I update company name to :name and location to :location
     */
    public function iUpdateCompanyNameAndLocation($name, $location)
    {
        $this->fillField('company_name', $name);
        $this->fillField('location', $location);
    }

    /**
     * @When I update industry description to :description
     */
    public function iUpdateIndustryDescription($description)
    {
        $this->fillField('industry_description', $description);
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
        sleep(2);
    }

    /**
     * @When I click on a post with applicants
     */
    public function iClickOnAPostWithApplicants()
    {
        $page = $this->getSession()->getPage();
        // Click "View Rankings" link/button for a post that has applicants
        $link = $page->find('xpath', '//a[contains(text(),"View Rankings")] | //button[contains(text(),"View Rankings")]');
        if (!$link) {
            $link = $page->find('xpath', '//a[contains(text(),"Applicants")] | //button[contains(text(),"Applicants")]');
        }
        if ($link) {
            $link->click();
            sleep(3);
        } else {
            throw new \Exception('Could not find a post with applicants');
        }
    }

    /**
     * @When I click :action for the first applicant
     */
    public function iClickActionForTheFirstApplicant($action)
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('xpath', '//button[contains(text(),"View Details")]');
        if ($button) {
            $button->click();
            sleep(2);
        } else {
            throw new \Exception("Could not find '$action' button");
        }
    }

    /**
     * @Then I should see applicant details
     */
    public function iShouldSeeApplicantDetails()
    {
        sleep(2);
        $page = $this->getSession()->getPage();
        $text = $page->getText();
        if (strpos($text, 'Score') === false && strpos($text, 'Candidate') === false && strpos($text, 'Hide Details') === false) {
            throw new \Exception('Applicant details not visible. Page text: ' . substr($text, 0, 200));
        }
    }

 

 
    /**
     * @Given I click logout
     */
    public function iClickLogout()
    {
        $page = $this->getSession()->getPage();
        $logout = $page->find('css', 'button[class*="logout"], a[href*="logout"]');
        if ($logout) {
            $logout->click();
            sleep(1);
            $confirm = $page->find('css', 'button[class*="confirm"]');
            if ($confirm) {
                $confirm->click();
                sleep(2);
            }
        } else {
            $this->visitPath('/logout');
        }
        $this->visitPath('/login');
        sleep(2);
    }

    /**
     * @Then I should be on the worker jobs page
     */
    public function iShouldBeOnTheWorkerJobsPage()
    {
        sleep(2);
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
        $field = $page->find('css', 'input[placeholder="e.g. Vue.js"]');
        if ($field) {
            $field->setValue($skill);
            sleep(1);
        } else {
            throw new \Exception('Skill search field not found');
        }
    }

    /**
     * @When I clear the skill search
     */
    public function iClearTheSkillSearch()
    {
        $page = $this->getSession()->getPage();
        $field = $page->find('css', 'input[placeholder="e.g. Vue.js"]');
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
        $button = $page->find('xpath', '//button[contains(text(),"' . $action . '")] | //a[contains(text(),"' . $action . '")]');
        if ($button) {
            $button->click();
            sleep(2);
        } else {
            throw new \Exception("Could not find '$action' button or link");
        }
    }

    /**
     * @Then I should be on the application page
     */
    public function iShouldBeOnTheApplicationPage()
    {
        sleep(2);
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, 'test-application') === false && strpos($url, 'apply') === false) {
            throw new \Exception('Not on application page. URL: ' . $url);
        }
    }
    
    /**
     * @When I toggle :label jobs only
     */
    public function iToggleJobsOnly($label)
    {
        $page = $this->getSession()->getPage();
        $toggle = $page->find('css', '#matchToggle');
        if ($toggle) {
            $toggle->click();
            sleep(1);
        } else {
            throw new \Exception("Could not find '$label' toggle");
        }
    }

    /**
     * @When I navigate to :path
     */
    public function visit($path)
    {
        $this->visitPath($path);
        sleep(2);
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
        sleep(5);
        echo "\nURL: " . $this->getSession()->getCurrentUrl();
        echo "\nPAGE: " . $this->getSession()->getPage()->getText();
        
        // Check browser console logs
        $logs = $this->getSession()->getDriver()->getWebDriverSession()->log('browser');
        foreach ($logs as $log) {
            echo "\nCONSOLE: " . $log['message'];
        }
    }
}