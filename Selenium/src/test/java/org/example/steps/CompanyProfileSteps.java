package org.example.steps;

import io.cucumber.java.en.*;
import org.example.base.BaseTest;
import org.example.pages.CompanyProfilePage;
import org.junit.jupiter.api.Assertions;

public class CompanyProfileSteps {
    private CompanyProfilePage profilePage = new CompanyProfilePage(BaseTest.getDriver());

    @Given("I am on the Company Profile Page")
    public void i_am_on_the_company_profile_page() {

            profilePage.navigateToProfile();
            System.out.println("✅ Navigated to Company Profile Page");



    }

    @When("I update company name to {string} and location to {string}")
    public void i_update_company_name_and_location(String name, String location) {
        profilePage.enterCompanyName(name);
        profilePage.enterLocation(location);
        System.out.println("✅ Entered name: " + name + ", location: " + location);
    }

    @When("I update industry description to {string}")
    public void i_update_industry_description(String description) {
        profilePage.enterDescription(description);
        System.out.println("✅ Entered description: " + description);
    }

    @When("I click 'Save Profile' button")
    public void i_click_save_profile_button() {
        profilePage.clickSaveProfile();
        System.out.println("✅ Clicked Save Profile");
    }

    @Then("I should see success message {string}")
    public void i_should_see_success_message(String expectedMessage) {
        // Add explicit wait for backend
        try {
            Thread.sleep(2000);
        } catch (InterruptedException e) {}

        boolean isDisplayed = profilePage.isSuccessMessageDisplayed(expectedMessage);
        Assertions.assertTrue(isDisplayed, "Success message not displayed or mismatch. Expected: " + expectedMessage);
        System.out.println("✅ Success message validated: " + expectedMessage);
    }


}