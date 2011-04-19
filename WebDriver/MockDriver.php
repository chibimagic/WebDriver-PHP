<?php

class WebDriver_MockDriver extends WebDriver_Driver {
  private $next_element_id;
  
  public function __construct() {
    try {
      parent::__construct("http://localhost/wd/hub");
    } catch (Exception $e) {
      // Will fail because there's no session id. Ignore.
    }
    $this->session_id = "314159265";
    $this->next_element_id = 0;
  }
  
  public function get_url() {
    return true;
  }
  
  public function get_title() {
    return true;
  }
  
  public function get_element($locator) {
    return new WebDriver_WebElement($this, $this->next_element_id++, $locator);
  }
}
