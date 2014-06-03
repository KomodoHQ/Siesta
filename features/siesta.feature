Feature: siesta

    To consume a REST Api I need to be able to extend my PHP class with the traits of Siesta and use
    them to pull data.

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
        Then the response should be an "array"
            And the length should be "2"
            And the items should be instances of "User"
            And the results' "name" properties should equal:
                """
                Will McKenzie
                Alan Mitchell
                """

    Scenario: Call find() method with arguments
        When I call static method "find" with arguments:
            """
            [
                {
                    'name': 'Alan Mitchell'
                }
            ]
            """
        Then the response should be an "array"
            And the length should be "1"
            And the items should be instances of "User"
            And the results' "name" properties should equal:
                """
                Alan Mitchell
                """

    Scenario: Call findOne() method
        When I call static method "findOne"
        Then the item should be an instance of "User"
            And the item's "name" property should equal "Will McKenzie"

    Scenario: Call findById() method
        When I call static method "findById" with arguments:
            """
            [1]
            """
            Then the item should be an instance of "User"
                And the item's "name" property should equal "Alan Mitchell"

    Scenario: Call create() method
        When I call static method "create" with arguments:
            """
            [
                {
                    'id':2,
                    'name':'James Gauld',
                    'email':'james@komododigital.co.uk'
                }
            ]
            """
        Then the item should be an instance of "User"
            And the item's "name" property should equal "James Gauld"

    Scenario: Call update() method
        Given I have an instance of "User" that extends Siesta
        When I call instance method "update" with arguments:
            """
            [
                {
                    'email': 'willmckenzie@komododigital.co.uk'
                }
            ]
            """
        Then the item should be an instance of "User"
            And the item's "email" property should equal "willmckenzie@komododigital.co.uk"

    Scenario: Call save() method
        Given I have an instance of "User" that extends Siesta
        When I set the "name" property to "Inigo Montoya"
        And I call instance method "save"
        Then the item's "name" property should equal "Inigo Montoya"

    Scenario: Call delete() method
        Given I have an instance of "User" that extends Siesta
        When I call instance method "delete"
        Then the item's "deleted" key should equal "1"
