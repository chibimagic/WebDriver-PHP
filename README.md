# Overview

These are the PHP bindings for the WebDriver API in Selenium 2. It's designed to work with PHPUnit and includes some built-ins for running tests at Sauce Labs.

For more information, see:

* [Selenium](http://code.google.com/p/selenium/)
* [PHPUnit](https://github.com/sebastianbergmann/phpunit/)
* [Sauce Labs](https://saucelabs.com/)

# Usage

See the included SampleTest.php. Start up the Selenium 2 standalone server (http://code.google.com/p/selenium/downloads/list) and run the test with:

    phpunit SampleTest.php

Make sure phpunit is in your path!

# Tests

What's code without tests? Run the tests with:

    phpunit WebDriverSelectorTest.php
    phpunit WebDriverXPathTest.php
    phpunit WebDriverColorTest.php
