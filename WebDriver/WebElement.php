<?php

class WebDriver_WebElement {
  protected $driver;
  protected $element_id;
  protected $locator;
  
  public function __construct($driver, $element_id, $locator) {
    $this->driver = $driver;
    $this->element_id = $element_id;
    $this->locator = $locator;
  }
  
  public function execute($http_type, $relative_url, $payload = null) {
    try {
      return $this->driver->execute($http_type, "/session/:sessionId/element/" . $this->element_id . $relative_url, $payload);
    } catch (WebDriver_StaleElementReferenceException $e) {
      $element_in_new_dom = $this->driver->get_element($this->locator);
      return $element_in_new_dom->execute($http_type, $relative_url, $payload);
    } catch (WebDriver_ElementNotVisibleException $e) {
      throw new WebDriver_ElementNotVisibleException($e->get_command(), $this->locator);
    }
  }
  
  public function get_driver() {
    return $this->driver;
  }
  
  public function get_element_id() {
    return $this->element_id;
  }
  
  public function get_locator() {
    return $this->locator;
  }
  
  /********************************************************************
   * Getters
   */
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id
  public function describe() {
    $response = $this->execute("GET", "");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/text
  public function get_text() {
    $response = $this->execute("GET", "/text");
    return trim(WebDriver::GetJSONValue($response));
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/value
  public function get_value() {
    $response = $this->execute("GET", "/value");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/displayed
  public function is_visible() {
    $response = $this->execute("GET", "/displayed");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/enabled
  public function is_enabled() {
    $response = $this->execute("GET", "/enabled");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/selected
  public function is_selected() {
    $response = $this->execute("GET", "/selected");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/element
  public function get_next_element($locator) {
    $payload = WebDriver::ParseLocator($locator);
    $response = $this->execute("POST", "/element", $payload);
    $next_element_id = WebDriver::GetJSONValue($response, "ELEMENT");
    return new WebDriver_WebElement($this->driver, $next_element_id, $locator);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/elements
  public function get_all_next_elements($locator) {
    $payload = WebDriver::ParseLocator($locator);
    $response = $this->execute("POST", "/elements", $payload);
    $all_element_ids = WebDriver::GetJSONValue($response, "ELEMENT");
    $all_elements = array();
    foreach ($all_element_ids as $element_id) {
      $all_elements[] = new WebDriver_WebElement($this->driver, $element_id, $locator);
    }
    return $all_elements;
  }
  
  public function get_next_element_count($locator) {
    return count($this->get_all_next_elements($locator));
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/name
  public function get_tag_name() {
    $response = $this->execute("GET", "/name");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/attribute/:name
  public function get_attribute_value($attribute_name) {
    $response = $this->execute("GET", "/attribute/" . $attribute_name);
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/equals/:other
  public function is_same_element_as($other_element_id) {
    $response = $this->execute("GET", "/equals/" . $other_element_id);
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/location
  public function get_location() {
    $response = $this->execute("GET", "/location");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/location_in_view
  public function get_location_in_view() {
    $response = $this->execute("GET", "/location_in_view");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/size
  public function get_size() {
    $response = $this->execute("GET", "/size");
    return WebDriver::GetJSONValue($response);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/css/:propertyName
  public function get_css_value($property_name) {
    $response = $this->execute("GET", "/css/" . $property_name);
    return WebDriver::GetJSONValue($response);
  }
  
  public function contains_element($locator) {
    try {
      $this->get_next_element($locator);
      $is_element_present = true;
    } catch (Exception $e) {
      $is_element_present = false;
    }
    return $is_element_present;
  }

  /********************************************************************
   * Getters for <select> elements
   */
  
  public function get_selected() {
    foreach ($this->get_options() as $option) {
      if ($option->is_selected()) {
        return $option;
      }
    }
  }
  
  public function get_all_selected() {
    $selected = array();
    foreach ($this->get_options() as $option) {
      if ($option->is_selected()) {
        $selected[] = $option;
      }
    }
    return $selected;
  }
  
  public function get_selected_count() {
    return count($this->get_all_selected());
  }
  
  // 1-based index
  public function get_option_index($index) {
    return $this->get_next_element("//option[$index]");
  }
  
  public function get_option_value($value) {
    return $this->get_next_element("//option[@value=" . WebDriver::QuoteXPath($value) . "]");
  }
  
  public function get_option_label($label) {
    return $this->get_next_element("//option[text()=" . WebDriver::QuoteXPath($label) . "]");
  }
  
  public function get_options() {
    return $this->get_all_next_elements("tag name=option");
  }
  
  public function get_option_count() {
    return count($this->get_options());
  }
  
  /********************************************************************
   * Setters
   */
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/click
  public function click() {
    $this->execute("POST", "/click");
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/submit
  public function submit() {
    $this->execute("POST", "/submit");
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/clear
  public function clear() {
    $this->execute("POST", "/clear");
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/hover
  public function hover() {
    $this->move_cursor_to_center(); // Workaround until /hover is implemented
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/selected
  public function select() {
    $this->click(); // POST /session/:sessionId/element/:id/selected is deprecated as of Selenium 2.0.0
  }

  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/value
  public function send_keys($keys) {
    $payload = array("value" => preg_split('//u', $keys, -1, PREG_SPLIT_NO_EMPTY));
    $this->execute("POST", "/value", $payload);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/moveto
  public function move_cursor_to_center() {
    $payload = array("element" => $this->element_id);
    $this->driver->execute("POST", "/session/:sessionId/moveto", $payload);
  }
  
  // See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/moveto
  public function move_cursor_relative($right, $down) {
    $payload = array(
      "element" => $this->element_id,
      "xoffset" => $right,
      "yoffset" => $down,
    );
    $this->driver->execute("POST", "/session/:sessionId/moveto", $payload);
  }
  
  /********************************************************************
   * Setters for <select> elements
   */

  public function select_label($label) {
    $this->get_next_element("//option[text()=" . WebDriver::QuoteXPath($label) . "]")->select();
  }
  
  public function select_value($value) {
    $this->get_next_element("//option[@value=" . WebDriver::QuoteXPath($value) . "]")->select();
  }
  
  // 1-based index
  public function select_index($index) {
    $this->get_next_element("//option[" . $index . "]")->select();
  }
  
  public function select_random() {
    $all_elements = $this->get_options();
    $new_index = rand(1, count($all_elements));
    $this->select_index($new_index);
  }
  
  /********************************************************************
   * Asserters
   */

  public function assert_visible() {
    $visible = WebDriver::WaitUntil(array($this, 'is_visible'), array(), true);
    PHPUnit_Framework_Assert::assertTrue($visible, "Failed asserting that <{$this->locator}> is visible.");
  }
  
  public function assert_hidden() {
    $visible = WebDriver::WaitUntil(array($this, 'is_visible'), array(), false);
    PHPUnit_Framework_Assert::assertFalse($visible, "Failed asserting that <{$this->locator}> is hidden.");
  }

  public function assert_enabled() {
    $enabled = WebDriver::WaitUntil(array($this, 'is_enabled'), array(), true);
    PHPUnit_Framework_Assert::assertTrue($enabled, "Failed asserting that <{$this->locator}> is enabled.");
  }
  
  public function assert_disabled() {
    $enabled = WebDriver::WaitUntil(array($this, 'is_enabled'), array(), false);
    PHPUnit_Framework_Assert::assertFalse($enabled, "Failed asserting that <{$this->locator}> is disabled.");
  }
  
  public function assert_selected() {
    $selected = WebDriver::WaitUntil(array($this, 'is_selected'), array(), true);
    PHPUnit_Framework_Assert::assertTrue($selected, "Failed asserting that <{$this->locator}> is selected.");
  }
  
  public function assert_not_selected() {
    $selected = WebDriver::WaitUntil(array($this, 'is_selected'), array(), false);
    PHPUnit_Framework_Assert::assertFalse($selected, "Failed asserting that <{$this->locator}> is not selected.");
  }
  
  public function assert_contains_element($child_locator) {
    $contains = WebDriver::WaitUntil(array($this, 'contains_element'), array($child_locator), true);
    PHPUnit_Framework_Assert::assertTrue($contains, "Failed asserting that <{$this->locator}> contains <$child_locator>.");
  }
  
  public function assert_does_not_contain_element($child_locator) {
    $contains = WebDriver::WaitUntil(array($this, 'contains_element'), array($child_locator), false);
    PHPUnit_Framework_Assert::assertFalse($contains, "Failed asserting that <{$this->locator}> does not contain <$child_locator>.");
  }
  
  public function assert_next_element_count($locator, $expected_count) {
    $count = WebDriver::WaitUntil(array($this, 'get_next_element_count'), array($locator), $expected_count);
    PHPUnit_Framework_Assert::assertEquals($expected_count, $count, "Failed asserting that <{$this->locator}> is followed by <$expected_count> instances of <$locator>");
  }
  
  public function assert_text($expected_text) {
    $text = WebDriver::WaitUntil(array($this, 'get_text'), array(), $expected_text);
    PHPUnit_Framework_Assert::assertEquals($expected_text, $text, "Failed asserting that <{$this->locator}>'s text is <$expected_text>.");
  }
  
  // Reload the page until this element's text matches what's expected
  public function assert_text_reload($expected_text) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $this->driver->reload();
      $element_in_new_dom = $this->driver->get_element($this->locator);
      $actual_text = $element_in_new_dom->get_text();
    } while (time() < $end_time && $actual_text != $expected_text);
    PHPUnit_Framework_Assert::assertEquals($expected_text, $actual_text, "Failed asserting that <{$this->locator}>'s text is <$expected_text>.");
  }
  
  public function assert_text_contains($expected_needle) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $actual_haystack = $this->get_text();
    } while (time() < $end_time && strstr($actual_haystack, $expected_needle) === false);
    PHPUnit_Framework_Assert::assertContains($expected_needle, $actual_haystack, "Failed asserting that <{$this->locator}>'s text contains <$expected_needle>.\n$actual_haystack");
  }

  public function assert_text_does_not_contain($expected_missing_needle) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $actual_haystack = $this->get_text();
    } while (time() < $end_time && strstr($actual_haystack, $expected_missing_needle) !== false);
    PHPUnit_Framework_Assert::assertNotContains($expected_missing_needle, $actual_haystack, "Failed asserting that <{$this->locator}>'s text does not contain <$expected_missing_needle>.");
  }

  public function assert_value($expected_value) {
    $value = WebDriver::WaitUntil(array($this, 'get_value'), array(), $expected_value);
    PHPUnit_Framework_Assert::assertEquals($expected_value, $value, "Failed asserting that <{$this->locator}>'s value is <$expected_value>.");
  }
  
  public function assert_attribute_value($attribute_name, $expected_value) {
    $value = WebDriver::WaitUntil(array($this, 'get_attribute_value'), array($attribute_name), $expected_value);
    PHPUnit_Framework_Assert::assertEquals($expected_value, $value, "Failed asserting that <{$this->locator}>'s attribute <{$attribute_name}> is <$expected_value>.");
  }
  
  public function assert_attribute_contains($attribute_name, $expected_needle) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $actual_haystack = $this->get_attribute_value($attribute_name);
    } while (time() < $end_time && strstr($actual_haystack, $expected_needle) === false);
    PHPUnit_Framework_Assert::assertContains($expected_needle, $actual_haystack, "Failed asserting that <{$this->locator}>'s attribute <{$attribute_name}> contains <{$expected_needle}>.");
  }
  
  public function assert_attribute_does_not_contain($attribute_name, $expected_missing_needle) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $actual_haystack = $this->get_attribute_value($attribute_name);
    } while (time() < $end_time && strstr($actual_haystack, $expected_missing_needle) !== false);
    PHPUnit_Framework_Assert::assertNotContains($expected_missing_needle, $actual_haystack, "Failed asserting that <{$this->locator}>'s attribute <{$attribute_name}> does not contain <{$expected_missing_needle}>.");
  }
  
  // Will pass for "equivalent" CSS colors such as "#FFFFFF" and "white". Pass $canonicalize_colors = false to disable.
  public function assert_css_value($property_name, $expected_value, $canonicalize_colors = true) {
    if (strpos($property_name, 'color') !== false && $canonicalize_colors) {
      $canonical_expected = WebDriver::CanonicalizeCSSColor($expected_value);
      $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
      do {
        $actual_value = $this->get_css_value($property_name);
        $canonical_actual = WebDriver::CanonicalizeCSSColor($actual_value);
      } while (time() < $end_time && $canonical_actual != $canonical_expected);
      PHPUnit_Framework_Assert::assertEquals($canonical_expected, $canonical_actual, "Failed asserting that <{$this->locator}>'s <{$property_name}> is <$canonical_expected> after canonicalization.\nExpected: $expected_value -> $canonical_expected\nActual: $actual_value -> $canonical_actual");
    } else {
      $value = WebDriver::WaitUntil(array($this, 'get_css_value'), array($property_name), $expected_value);
      PHPUnit_Framework_Assert::assertEquals($expected_value, $value, "Failed asserting that <{$this->locator}>'s <{$property_name}> is <$expected_value>.");
    }
  }
  
  public function assert_css_value_contains($property_name, $expected_needle) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    do {
      $actual_haystack = $this->get_css_value($property_name);
    } while (time() < $end_time && strstr($actual_haystack, $expected_needle) === false);
    PHPUnit_Framework_Assert::assertContains($expected_needle, $actual_haystack, "Failed asserting that <{$this->locator}>'s <{$property_name}> contains <$expected_needle>.\n$actual_haystack");
  }
  
  /********************************************************************
   * Asserters for <select> elements
   */
  
  public function assert_option_count($expected_count) {
    $count = WebDriver::WaitUntil(array($this, 'get_option_count'), array(), $expected_count);
    PHPUnit_Framework_Assert::assertEquals($expected_count, $count, "Failed asserting that <{$this->locator}> contains $expected_count options.");
  }
  
  public function assert_selected_count($expected_count) {
    $count = WebDriver::WaitUntil(array($this, 'get_selected_count'), array(), $expected_count);
    PHPUnit_Framework_Assert::assertEquals($expected_count, $count, "Failed asserting that <{$this->locator}> contains $expected_count selected options.");
  }
  
  public function assert_contains_label($expected_label) {
    $end_time = time() + WebDriver::$ImplicitWaitMS/1000;
    $contains_label = false;
    do {
      $options = $this->get_options();
      $labels = array();
      foreach ($options as $option) {
        $actual_label = $option->get_text();
        if ($actual_label == $expected_label) {
          $contains_label = true;
          break;
        }
        $labels[] = $actual_label;
      }
    } while (time() < $end_time && !$contains_label);
    PHPUnit_Framework_Assert::assertTrue($contains_label, "Failed asserting that <{$this->locator}> contains label <$expected_label>.\n" . print_r($labels, true));
  }
}
