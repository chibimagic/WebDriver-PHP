<?php

class WebDriver_ElementNotVisibleException extends PHPUnit_Framework_ExpectationFailedException {
  protected $command;
  
  public function __construct($command = '', $locator = '') {
    $this->command = $command;
    $locator_description = strlen($locator) > 0 ? '<' . $locator . '>' : 'this element';
    parent::__construct("Could not interact with {$locator_description} because it is not visible\nCommand: $command");
  }
  
  public function get_command() {
    return $this->command;
  }
}
