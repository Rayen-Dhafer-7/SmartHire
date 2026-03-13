package org.example.steps;

import io.cucumber.java.After;
import io.cucumber.java.Before;
import io.cucumber.java.Scenario;
import org.example.base.BaseTest;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;

public class Hooks {

    @Before
    public void setup() {
        try {
            BaseTest.initializeDriver();
        } catch (Exception e) {
            throw new RuntimeException("Driver initialization failed", e);
        }
    }

    @After
    public void tearDown(Scenario scenario) {
        WebDriver driver = null;

        try {
            driver = BaseTest.getDriver();

            // Take screenshot ONLY if scenario failed
            if (scenario.isFailed() && driver != null) {
                byte[] screenshot = ((TakesScreenshot) driver).getScreenshotAs(OutputType.BYTES);
                scenario.attach(screenshot, "image/png", "Failed Scenario Screenshot");
                System.out.println("📸 Screenshot attached to failed scenario: " + scenario.getName());
            }

        } catch (Exception e) {
            System.out.println("Hook error: " + e.getMessage());
        } finally {
            // Commented out to maintain session as per user request
            // if (driver != null) {
            // BaseTest.quitDriver();
            // }
        }
    }
}
