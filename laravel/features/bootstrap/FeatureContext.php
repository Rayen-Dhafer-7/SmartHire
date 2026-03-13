<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    public function __construct()
    {
        // Wait for Selenium container to start
        sleep(5);
    }

    /**
     * @Given /^I am on the Login Page$/
     */
    public function iAmOnTheLoginPage()
    {
        $this->visitPath('/login');
    }

    /**
     * @When /^I enter valid email "([^"]*)" and password "([^"]*)"$/
     */
    public function iEnterValidEmailAndPassword($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);
    }

    /**
     * @When /^I click '([^']*)' button$/
     * @When /^I click '([^']*)'$/
     */
    public function iClickButton($button)
    {
        $this->pressButton($button);
    }

    /**
     * @Then /^I should see error message "([^"]*)"$/
     */
    public function iShouldSeeErrorMessage($message)
    {
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Then /^I should be logged in$/
     */
    public function iShouldBeLoggedIn()
    {
        $this->assertSession()->pageTextContains('Dashboard');
    }

    /**
     * @Given /^I am on the Company Profile Page$/
     */
    public function iAmOnTheCompanyProfilePage()
    {
        $this->visitPath('/company/profile');
    }

    /**
     * @When /^I update company name to "([^"]*)" and location to "([^"]*)"$/
     */
    public function iUpdateCompanyNameAndLocation($name, $location)
    {
        $this->fillField('company_name', $name);
        $this->fillField('location', $location);
    }

    /**
     * @When /^I update industry description to "([^"]*)"$/
     */
    public function iUpdateIndustryDescription($description)
    {
        $this->fillField('industry_description', $description);
    }

    /**
     * @Then /^I should see success message "([^"]*)"$/
     */
    public function iShouldSeeSuccessMessage($message)
    {
        $this->assertSession()->pageTextContains($message);
    }

    /**
     * @Given /^I am on the Old Posts Page$/
     */
    public function iAmOnTheOldPostsPage()
    {
        $this->visitPath('/company/old-posts');
    }

    /**
     * @When /^I click on a post with applicants$/
     */
    public function iClickOnAPostWithApplicants()
    {
        $this->getSession()->getPage()->clickLink('Applicants');
    }

    /**
     * @When /^I click '([^']*)' for the first applicant$/
     */
    public function iClickActionForTheFirstApplicant($action)
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('css', '.applicant-list .btn:contains("' . $action . '")');
        
        if (!$button) {
            $button = $page->findLink($action);
        }

        if ($button) {
            $button->click();
        } else {
            throw new \Exception("Could not find button or link with text '$action'");
        }
    }

    /**
     * @Then /^I should see applicant details$/
     */
    public function iShouldSeeApplicantDetails()
    {
        $this->assertSession()->pageTextContains('Applicant Details');
    }

    /**
     * @Given /^I click logout$/
     */
    public function iClickLogout()
    {
        $this->visitPath('/logout');
    }

    /**
     * @Then /^I should be on the worker jobs page$/
     */
    public function iShouldBeOnTheWorkerJobsPage()
    {
        $this->assertSession()->addressEquals($this->locatePath('/worker/jobs'));
    }

    /**
     * @When /^I search for skill "([^"]*)"$/
     */
    public function iSearchForSkill($skill)
    {
        $this->fillField('skill-search', $skill);
    }

    /**
     * @When /^I clear the skill search$/
     */
    public function iClearTheSkillSearch()
    {
        $this->fillField('skill-search', '');
    }

    /**
     * @When /^I click '([^']*)' for a job$/
     */
    public function iClickActionForAJob($action)
    {
        $this->clickLink($action);
    }

    /**
     * @Then /^I should be on the application page$/
     */
    public function iShouldBeOnTheApplicationPage()
    {
        $this->assertSession()->pageTextContains('Job Application');
    }

    /**
     * @When /^I toggle '([^']*)' jobs only$/
     */
    public function iToggleJobsOnly($label)
    {
        $this->fillField('matched-toggle', '1');
    }
}
