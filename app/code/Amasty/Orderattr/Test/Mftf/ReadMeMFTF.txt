ReadeMeMFTF (recommendations for running tests related to Order Attributes extension).

    30 Order Attributes specific tests, grouped by purpose, for greater convenience.

        Tests group: Orderattr
        Runs all tests.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group Orderattr -r

        Tests group: CheckTextFieldOA
        Runs tests with Text Field type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckTextFieldOA -r

        Tests group: CheckTextAreaOA
        Runs tests with Text Area type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckTextAreaOA -r

        Tests group: CheckDateOA
        Runs tests with Date type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckDateOA -r

        Tests group: CheckDateWithTimeOA
        Runs tests with Date/Time type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckDateWithTimeOA -r

        Tests group: CheckDropdownOA
         Runs tests with Dropdown type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckDropdownOA -r

        Tests group: CheckMultipleSelectOA
         Runs tests with Multiple Select type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckMultipleSelectOA -r

        Tests group: CheckRadioButtonsOA
         Runs tests with Radio Buttons type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckRadioButtonsOA -r

        Tests group: CheckYesNoOA
         Runs tests with Yes/No type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckYesNoOA -r

        Tests group: CheckCheckboxOA
         Runs tests with Checkbox Group type of Order Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckCheckboxOA -r

        Tests group: CheckHtmlOA
         Runs tests with Html type Order of Attribute.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group CheckHtmlOA -r