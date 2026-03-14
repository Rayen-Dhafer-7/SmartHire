Feature: Login
  As a user I want to login to Smarthire

  Scenario: invalid login - incorrect password
    Given I am on the Login Page
    When I enter valid email "dd@dd.dd" and password "wrongpass"
    And I click 'Sign In' button
    Then I should not see "inprogress-posts"

  Scenario: valid company login
    Given I am on the Login Page
    When I enter valid email "dd@dd.dd" and password "123456"
    And I click 'Sign In' button
    Then I should see the page
    

  Scenario: update company profile
    Given I am on the Company Profile Page
    When I update company name to "Updated Company" and location to "New York"
    And I update industry description to "Updated industry description"
    And I click 'Save Profile' button
    Then I should see profile saved

  Scenario: view applicant details from old posts
    Given I am on the Old Posts Page
    When I click on a post with applicants
    And I click 'View Details' for the first applicant
    Then I should see applicant details

  Scenario: login worker
    Given I click logout
    And I enter valid email "rayendhafer@gmail.com" and password "123456"
    And I click 'Sign In' button
    Then I should be logged in

  Scenario: liste jobs and search par skills
    And I navigate to "/worker/jobs"
    Then I should be on the worker jobs page
    When I search for skill "react js"
    And I clear the skill search

  Scenario: test apply
    And I click 'Apply Now' for a job
    Then I should be on the application page

  Scenario: test matched jobs
    And I navigate to "/worker/jobs"
    When I toggle 'Matched' jobs only