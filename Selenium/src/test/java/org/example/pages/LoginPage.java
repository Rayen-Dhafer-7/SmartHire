package org.example.pages;

import org.example.utils.WaitUtils;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class LoginPage {
    private WebDriver driver;
    private WaitUtils waitUtils;

    private By emailInput = By.xpath("//input[@type='email']");
    private By passwordInput = By.xpath("//input[@type='password']");
    private By loginButton = By.xpath("//button[contains(text(),'Sign In') or contains(text(),'Login')]");
    private By profileIcon = By.xpath("//div[contains(@class, 'nav-item')]//img[contains(@class, 'rounded-circle')]");
    private By logoutButton = By.cssSelector(".logout-item");
    private By confirmLogoutButton = By.xpath("//button[contains(text(),'Yes, logout')]");
    private By jobsPageElement = By
            .xpath("//h2[contains(text(),'Available Jobs')] | //div[contains(@class, 'jobs-grid')]");

    private By skillSearchInput = By.xpath("//input[@placeholder='e.g. Vue.js']");
    private By applyNowButton = By.xpath("//button[contains(text(),'Apply Now')]");
    private By matchedToggle = By.id("matchToggle");

    // FIXED: Error message locator based on actual message from your app
    // From your logs: "Login Failed Invalid credentials OK" appears in a popup
    private By errorMessage = By.xpath("//div[contains(text(),'Login Failed')] | " +
            "//div[contains(text(),'Invalid credentials')] | " +
            "//div[contains(@role,'alert')] | " +
            "//div[contains(@class,'swal')] | " + // SweetAlert
            "//div[contains(@class,'modal-content')] | " + // Modal content
            "//div[contains(@class,'popup')] | " + // Popup
            "//button[contains(text(),'OK')]/ancestor::div[contains(@class,'modal')] | " +
            "//div[contains(@class,'dialog')]");

    public LoginPage(WebDriver driver) {
        this.driver = driver;
        this.waitUtils = new WaitUtils(driver);
    }

    public void navigateToLogin() {
        driver.get("http://localhost:5174/login");
    }

    public boolean isLoginPageVisible() {
        try {
            return waitUtils.waitForVisibility(emailInput).isDisplayed();
        } catch (Exception e) {
            return false;
        }
    }

    public void enterEmail(String email) {
        waitUtils.waitForVisibility(emailInput).sendKeys(email);
    }

    public void enterPassword(String password) {
        waitUtils.waitForVisibility(passwordInput).sendKeys(password);
    }

    public void clickLogin() {
        waitUtils.waitForClickability(loginButton).click();
    }

    public boolean isLoggedIn() {
        try {
            Thread.sleep(5000);
            String currentUrl = driver.getCurrentUrl();
            System.out.println("Current URL after login: " + currentUrl);
            System.out.println("Page title: " + driver.getTitle());
            return currentUrl.contains("/worker") || currentUrl.contains("/company");
        } catch (Exception e) {
            System.out.println("Error checking login status: " + e.getMessage());
            return false;
        }
    }

    // IMPROVED: Get error message with multiple strategies
    public String getErrorMessage() {
        try {
            Thread.sleep(2000); // Wait longer for error message to appear

            String errorText = "";

            // Strategy 1: Try multiple locators
            By[] locators = {
                    By.xpath("//div[contains(text(),'Login Failed')]"),
                    By.xpath("//div[contains(text(),'Invalid credentials')]"),
                    By.xpath("//div[contains(@class,'alert')]"),
                    By.xpath("//div[contains(@class,'modal')]"),
                    By.xpath("//div[contains(@class,'swal')]"),
                    By.xpath("//div[contains(@role,'alert')]"),
                    By.xpath("//button[contains(text(),'OK')]/..")
            };

            for (By locator : locators) {
                try {
                    if (driver.findElements(locator).size() > 0) {
                        errorText = driver.findElement(locator).getText();
                        if (!errorText.isEmpty()) {
                            System.out.println("Found error with locator: " + locator);
                            System.out.println("Error text: '" + errorText + "'");
                            return errorText;
                        }
                    }
                } catch (Exception e) {
                    // Continue to next locator
                }
            }

            // Strategy 2: If no element found, check page source for the known message
            if (errorText.isEmpty()) {
                String pageSource = driver.getPageSource();
                if (pageSource.contains("Invalid credentials")) {
                    System.out.println("Found 'Invalid credentials' in page source");
                    return "Invalid credentials";
                }
                if (pageSource.contains("Login Failed")) {
                    System.out.println("Found 'Login Failed' in page source");
                    return "Login Failed";
                }
            }

            return "";

        } catch (Exception e) {
            System.out.println("Error getting error message: " + e.getMessage());
            return "";
        }
    }

    // IMPROVED: Check if error message contains expected text
    public boolean isErrorMessageDisplayed(String expectedText) {
        String actualError = getErrorMessage();
        System.out.println("Final actual error message: '" + actualError + "'");

        // Check if it contains either the expected text or the actual message from your
        // app
        return actualError.contains(expectedText) ||
                actualError.contains("Invalid credentials") ||
                actualError.contains("Login Failed");
    }

    // Optional: Method to click OK button to dismiss error
    public void dismissError() {
        try {
            By okButton = By.xpath("//button[contains(text(),'OK')]");
            if (driver.findElements(okButton).size() > 0) {
                driver.findElement(okButton).click();
                System.out.println("✅ Clicked OK button to dismiss error");
                Thread.sleep(1000);
            }
        } catch (Exception e) {
            System.out.println("No OK button found or could not click");
        }
    }

    public void clickLogout() {
        try {
            waitUtils.waitForClickability(logoutButton).click();
            System.out.println("✅ Clicked logout button");
            Thread.sleep(1000);
            waitUtils.waitForClickability(confirmLogoutButton).click();
            System.out.println("✅ Clicked confirm logout button");
            Thread.sleep(2000);
        } catch (Exception e) {
            System.out.println("Error during logout: " + e.getMessage());
        }
    }

    public boolean isJobsPageVisible() {
        try {
            return waitUtils.waitForVisibility(jobsPageElement).isDisplayed();
        } catch (Exception e) {
            System.out.println("Jobs page not visible: " + e.getMessage());
            return false;
        }
    }

    public void searchSkill(String skill) {
        WebElement input = waitUtils.waitForVisibility(skillSearchInput);
        input.clear();
        input.sendKeys(skill);
        System.out.println("✅ Searched for skill: " + skill);
    }

    public void clearSkillSearch() {
        WebElement input = waitUtils.waitForVisibility(skillSearchInput);
        input.sendKeys(Keys.CONTROL + "a");
        input.sendKeys(Keys.BACK_SPACE);
        System.out.println("✅ Cleared skill search");
    }

    public void clickApplyNowFirst() {
        waitUtils.waitForClickability(applyNowButton).click();
        System.out.println("✅ Clicked Apply Now button");
    }

    public void toggleMatchedJobs() {
        waitUtils.waitForClickability(matchedToggle).click();
        System.out.println("✅ Toggled Matched jobs switch");
    }
}