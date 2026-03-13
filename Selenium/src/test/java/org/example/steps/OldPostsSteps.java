package org.example.steps;

import io.cucumber.java.en.*;
import org.example.base.BaseTest;
import org.example.pages.OldPostsPage;
import org.example.pages.PostDetailsPage;
import org.junit.jupiter.api.Assertions;

public class OldPostsSteps {
    private OldPostsPage oldPostsPage = new OldPostsPage(BaseTest.getDriver());
    private PostDetailsPage postDetailsPage = new PostDetailsPage(BaseTest.getDriver());

    @Given("I am on the Old Posts Page")
    public void i_am_on_the_old_posts_page() {
        oldPostsPage.navigateToOldPosts();
        System.out.println("✅ Navigated to Old Posts Page");
    }

    @When("I click on a post with applicants")
    public void i_click_on_a_post_with_applicants() {
        oldPostsPage.clickPostWithApplicants();
        System.out.println("✅ Clicked on a post with applicants");
    }

    @When("I click 'View Details' for the first applicant")
    public void i_click_view_details_for_first_applicant() {
        postDetailsPage.clickViewDetailsForFirstApplicant();
        System.out.println("✅ Clicked View Details for the first applicant");
    }

    @Then("I should see applicant details")
    public void i_should_see_applicant_details() {
        boolean isVisible = postDetailsPage.isApplicantDetailsVisible();
        Assertions.assertTrue(isVisible, "Applicant details are not visible");
        System.out.println("✅ Applicant details verified");
    }
}
