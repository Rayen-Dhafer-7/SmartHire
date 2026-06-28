package org.example.pages;

import org.example.utils.WaitUtils;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import java.util.List;

public class OldPostsPage {
    private WebDriver driver;
    private WaitUtils waitUtils;

    // Selector for post card that has more than 0 applicants
    // The text is "X Applicants"
    private By postCards = By.cssSelector(".post-card");
    private By applicantsCount = By.cssSelector(".mt-3.pt-3.border-top div.small.fw-bold");

    public OldPostsPage(WebDriver driver) {
        this.driver = driver;
        this.waitUtils = new WaitUtils(driver);
    }

    public void navigateToOldPosts() {
        driver.get("http://localhost:5174/company/old-posts");
    }

    public void clickPostWithApplicants() {
        waitUtils.waitForVisibility(postCards);
        List<WebElement> cards = driver.findElements(postCards);

        for (WebElement card : cards) {
            String text = card.findElement(applicantsCount).getText();
            // text format: "5 Applicants"
            int count = Integer.parseInt(text.split(" ")[0]);
            if (count > 0) {
                card.click();
                return;
            }
        }
        throw new RuntimeException("No post with applicants found!");
    }
}
