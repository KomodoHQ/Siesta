Feature: siesta

    To consume a REST Api I need to be able to extend my PHP class with the traits of Siesta and use
    them to pull data.

    Background:
        Given: Given I have a class that extends Siesta

    Scenario: Extend PHP Class with Siesta traits
        When I query the methods
        Then it should have these methods:
            """
            find
            findOne
            findById
            create
            update
            save
            delete
            """

    Scenario: Call find() method
        When I call static method "find"
        Then the response should be a "array"
            And the length should be "2"
            And the items should be instances of "User"
            And the results' "name" properties should equal:
                """
                Will McKenzie
                Alan Mitchell
                """
