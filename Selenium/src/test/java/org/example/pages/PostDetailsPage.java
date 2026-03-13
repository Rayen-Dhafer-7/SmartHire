package org.example.pages;

import org.example.utils.WaitUtils;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.ElementClickInterceptedException;

public class PostDetailsPage {
    private WebDriver driver;
    private WaitUtils waitUtils;

    private By viewDetailsButtons = By.xpath("//button[contains(text(), 'View Details')]");



    private By candidateDetails = By.cssSelector(".bg-light td[colspan='5']");

    public PostDetailsPage(WebDriver driver) {
        this.driver = driver;
        this.waitUtils = new WaitUtils(driver);
    }

    public void clickViewDetailsForFirstApplicant() {
        try {
            WebElement button = waitUtils.waitForClickability(viewDetailsButtons);
            button.click();
        } catch (ElementClickInterceptedException e) {
            WebElement button = waitUtils.waitForVisibility(viewDetailsButtons);
            ((JavascriptExecutor) driver).executeScript("arguments[0].click();", button);
        }
    }

    public boolean isApplicantDetailsVisible() {
        try {
            return waitUtils.waitForVisibility(candidateDetails).isDisplayed();
        } catch (Exception e) {
            return false;
        }
    }
}
