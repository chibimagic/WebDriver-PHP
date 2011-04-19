<?php

require_once 'WebDriver.php';
require_once 'WebDriver/Driver.php';
require_once 'WebDriver/MockDriver.php';
require_once 'WebDriver/WebElement.php';

class SampleTest extends PHPUnit_Framework_TestCase {
  protected $driver;
  
  public function setUp() {
    // Choose one of the following
    
    // For tests running at Sauce Labs
//     $this->driver = WebDriver_Driver::InitAtSauce("my-sauce-username", "my-sauce-api-key", "WINDOWS", "firefox", "3.6");
//     $sauce_job_name = get_class($this);
//     $this->driver->set_sauce_context("name", $sauce_job_name);
    
    // For a mock driver (for debugging)
//     $this->driver = new WebDriver_MockDriver();

    // For a local driver
    $this->driver = WebDriver_Driver::InitAtLocal("4444", "firefox");
  }
  
  // Forward calls to main driver 
  public function __call($name, $arguments) {
    if (method_exists($this->driver, $name)) {
      return call_user_func_array(array($this->driver, $name), $arguments);
    }
  }

  public function test() {
    $this->load("http://seleniumhq.org/");
    $this->assert_title("Selenium web application testing system");
    $this->get_element("css=h2")->assert_text("Get started with Selenium!");
    
    $this->get_element("id=q")->send_keys("webdriver");
    $this->get_element("id=submit")->click();
    
    $first_result = $this->get_element("css=a.gs-title")->get_text();
  }
  
  public function tearDown() {
    if ($this->driver) {
      if ($this->hasFailed()) {
        $this->driver->set_sauce_context("passed", false);
      } else {
        $this->driver->set_sauce_context("passed", true);
      }
      $this->driver->quit();
    }
    parent::tearDown();
  }
}