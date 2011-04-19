<?php

require_once 'WebDriver.php';

class WebDriverSelectorTest extends PHPUnit_Framework_TestCase {
  public function valid_selectors() {
    return array(
      array("identifier=some_id", "id", "some_id"),
      array("id=some_other_id", "id", "some_other_id"),
      array("name=some_name", "name", "some_name"),
      array("xpath=//div[@class='some_class']", "xpath", "//div[@class='some_class']"),
      array("link=Click here", "link text", "Click here"),
      array("link=1 + 2 = 3", "link text", "1 + 2 = 3"),
      array("link text=Click here", "link text", "Click here"),
      array("link text=2+2=4", "link text", "2+2=4"),
      array("css=a.person_link", "css selector", "a.person_link"),
      array("css selector=div#main", "css selector", "div#main"),
      array("partial link text=nvite someon", "partial link text", "nvite someon"),
      array("tag name=li", "tag name", "li"),
      array("class=admin-msg", "class", "admin-msg"),
      array("class name=success-msg", "class name", "success-msg"),
      array("//table//td", "xpath", "//table//td"),
      array("//table[@class='edit']", "xpath", "//table[@class='edit']"),
      array("fakelocator=qwerqwer", "id", "fakelocator=qwerqwer"),
      array("asdfasdf", "id", "asdfasdf")
    );
  }
  
  /**
   * @dataProvider valid_selectors
   */
  public function test_valid_selectors($input, $expected_using, $expected_value) {
    $actual = WebDriver::ParseLocator($input);
    $this->assertEquals($actual["using"], $expected_using);
    $this->assertEquals($actual["value"], $expected_value);
  }
  
  public function invalid_selectors() {
    return array(
      array("dom=document.images[5]"),
      array("document.forms['myForm']")
    );
  }
  
  /**
   * @dataProvider invalid_selectors
   * @expectedException Exception
   */
  public function test_invalid_selectors($input) {
    WebDriver::ParseLocator($input);
  }
}
