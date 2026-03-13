package org.example.steps;

import io.cucumber.java.en.*;
import org.example.base.BaseTest;
import org.example.pages.LoginPage;
import org.junit.jupiter.api.Assertions;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;

public class LoginSteps {

    LoginPage loginPage = new LoginPage(BaseTest.getDriver());

    @Given("I am on the Login Page")
    public void i_am_on_the_login_page() {
        loginPage.navigateToLogin();
        Assertions.assertTrue(loginPage.isLoginPageVisible(), "Not on Login Page");
        System.out.println("✅ On Login Page - URL: " + BaseTest.getDriver().getCurrentUrl());
    }

    @When("I enter valid email {string} and password {string}")
    public void i_enter_valid_email_and_password(String email, String password) {
        loginPage.enterEmail(email);
        loginPage.enterPassword(password);
        System.out.println("✅ Entered credentials: " + email);
    }

    @When("I click 'login' button")
    public void i_click_login_button() {
        loginPage.clickLogin();

        // Add explicit wait for page load
        try {
            Thread.sleep(3000);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }

        System.out.println("✅ Clicked login button");
    }

    @Then("I should be logged in")
    public void i_should_be_logged_in() {
        // Check URL multiple times
        for (int i = 0; i < 10; i++) {
            try {
                Thread.sleep(1000);
                String url = BaseTest.getDriver().getCurrentUrl();
                System.out.println("URL check " + (i + 1) + ": " + url);

                if (url.contains("/worker") || url.contains("/company")) {
                    System.out.println("✅ Found profile URL!");
                    break;
                }
            } catch (Exception e) {
            }
        }

        boolean isLoggedIn = loginPage.isLoggedIn();
        System.out.println("🔍 Login check result: " + isLoggedIn);

        if (!isLoggedIn) {
            takeAndSaveScreenshot("login_failed");
        }

        Assertions.assertTrue(isLoggedIn, "User is not logged in");
    }

    @Then("I should see error message {string}")
    public void i_should_see_error_message(String expectedMessage) {
        // Take screenshot for debugging
        takeAndSaveScreenshot("invalid_login_error");

        // Wait a bit for error message to appear
        try {
            Thread.sleep(2000);
        } catch (InterruptedException e) {
        }

        // Use the improved error message method
        boolean hasError = loginPage.isErrorMessageDisplayed(expectedMessage);

        // Debug: Print page source snippet
        if (!hasError) {
            String pageSource = BaseTest.getDriver().getPageSource();
            System.out.println("🔍 Page source snippet (first 500 chars):");
            System.out.println(pageSource.substring(0, Math.min(500, pageSource.length())));

            // Take another screenshot
            takeAndSaveScreenshot("error_message_debug");
        }

        Assertions.assertTrue(hasError,
                "Error message mismatch. Expected to contain: '" + expectedMessage + "'");
    }

    @Given("I click logout")
    public void i_click_logout() {
        loginPage.clickLogout();
    }

    @And("I go to {string}")
    public void i_go_to(String path) {
        String baseUrl = "http://localhost:5174";
        BaseTest.getDriver().get(baseUrl + path);
        System.out.println("✅ Navigated to: " + baseUrl + path);
    }

    @Then("I should be on the worker jobs page")
    public void i_should_be_on_the_worker_jobs_page() {
        Assertions.assertTrue(loginPage.isJobsPageVisible(), "Jobs page is not visible");
        System.out.println("✅ On Worker Jobs Page");
    }

    @When("I search for skill {string}")
    public void i_search_for_skill(String skill) {
        loginPage.searchSkill(skill);
        try {
            Thread.sleep(2000);
        } catch (Exception e) {
        }
    }

    @And("I clear the skill search")
    public void i_clear_the_skill_search() {
        loginPage.clearSkillSearch();
        try {
            Thread.sleep(2000);
        } catch (Exception e) {
        }
    }

    @And("I click 'Apply Now' for a job")
    public void i_click_apply_now_for_a_job() {
        loginPage.clickApplyNowFirst();
        try {
            Thread.sleep(2000);
        } catch (Exception e) {
        }
    }

    @Then("I should be on the application page")
    public void i_should_be_on_the_application_page() {
        String url = BaseTest.getDriver().getCurrentUrl();
        Assertions.assertTrue(url.contains("/test-application/"), "Not on application page");
        System.out.println("✅ On Application Page");
    }

    @When("I toggle 'Matched' jobs only")
    public void i_toggle_matched_jobs_only() {
        loginPage.toggleMatchedJobs();
        try {
            Thread.sleep(2000);
        } catch (Exception e) {
        }
    }

    private void takeAndSaveScreenshot(String screenshotName) {
        try {
            if (BaseTest.getDriver() != null) {
                TakesScreenshot ts = (TakesScreenshot) BaseTest.getDriver();
                byte[] screenshot = ts.getScreenshotAs(OutputType.BYTES);

                System.out.println("📸 Screenshot taken: " + screenshotName);
                saveScreenshotToFile(screenshotName, screenshot);
            }
        } catch (Exception e) {
            System.out.println("Failed to take screenshot: " + e.getMessage());
        }
    }

    private void saveScreenshotToFile(String screenshotName, byte[] screenshot) {
        try {
            File screenshotsDir = new File("target/screenshots");
            if (!screenshotsDir.exists()) {
                screenshotsDir.mkdirs();
            }

            String timestamp = new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date());
            String fileName = screenshotName + "_" + timestamp + ".png";
            File screenshotFile = new File(screenshotsDir, fileName);

            try (FileOutputStream fos = new FileOutputStream(screenshotFile)) {
                fos.write(screenshot);
            }

            System.out.println("💾 Screenshot SAVED to: " + screenshotFile.getAbsolutePath());

        } catch (IOException e) {
            System.out.println("Failed to save screenshot file: " + e.getMessage());
        }
    }
}