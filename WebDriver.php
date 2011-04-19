<?php

class WebDriver {
  public static function Curl($http_type, $full_url, $payload = null) {
    $curl = curl_init($full_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_type);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);
    if (($http_type === "POST" || $http_type === "PUT") && $payload !== null) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    WebDriver::LogDebug($http_type, $full_url, $payload);
    $full_response = curl_exec($curl);
    WebDriver::LogDebug($full_response);
    curl_close($curl);
    $response_parts = explode("\r\n\r\n", $full_response, 2);
    $response['header'] = $response_parts[0];
    if (!empty($response_parts[1])) {
      $response['body'] = $response_parts[1];
    }
    return $response;
  }

  public static function ParseLocator($locator) {
    $se1_to_se2 = array(
      "identifier" => "id",
      "id" => "id",
      "name" => "name",
      "xpath" => "xpath",
      "link" => "link text",
      "css" => "css selector",
        // The dom selector in Se1 isn't in Se2
        // Se2 has 4 new selectors
        "partial link text",
        "tag name",
        "class",
        "class name"      
    );
    
    $locator_parts = explode("=", $locator, 2);
    if (array_key_exists($locator_parts[0], $se1_to_se2) && $locator_parts[1]) { // Explicit Se1 selector
      $strategy = $se1_to_se2[$locator_parts[0]];
      $value = $locator_parts[1];
    } else if (in_array($locator_parts[0], $se1_to_se2) && $locator_parts[1]) { // Explicit Se2 selector
      $strategy = $locator_parts[0];
      $value = $locator_parts[1];
    } else { // Guess the selector based on Se1
      if (substr($locator, 0, 2) === "//") {
        $strategy = "xpath";
        $value = $locator;
      } else if (substr($locator, 0, 9) === "document." || substr($locator, 0, 4) === "dom=") {
        throw new Exception("DOM selectors aren't supported in WebDriver: $locator");
      } else { // Fall back to id
        $strategy = "id";
        $value = $locator;
      }
    }
    return array("using" => $strategy, "value" => $value);
  }

  public static function GetJSONValue($curl_response, $attribute = null) {
    if (!isset($curl_response['body'])) {
      throw new Exception("Response had no body\n{$curl_response['header']}");
    }
    $array = json_decode(trim($curl_response['body']), true);
    if ($array === null) {
      throw new Exception("Body could not be decoded as JSON\n{$curl_response['body']}");
    }
    if (!isset($array["value"])) {
      throw new Exception("JSON had no value\n" . print_r($array, true));
    }
    if ($attribute === null) {
      $rv = $array["value"];
    } else {
      if (isset($array["value"][$attribute])) {
        $rv = $array["value"][$attribute];
      } else if (is_array($array["value"])) {
        $rv = array();
        foreach ($array["value"] as $a_value) {
          if (isset($a_value[$attribute])) {
            $rv[] = $a_value[$attribute];
          } else {
            throw new Exception("JSON value did not have attribute $attribute\n" . $array["value"]["message"]);
          }
        }
      } else {
        throw new Exception("JSON value did not have attribute $attribute\n" . $array["value"]["message"]);
      }
    }
    return $rv;
  }
  
  public static function LogDebug() {
    if (defined('kFestDebug') && kFestDebug) {
      $non_null = array_filter(func_get_args());
      $strings = 0;
      foreach ($non_null as $argument) {
        if (is_string($argument)) {
          $strings++;
        }
      }
      if ($strings == sizeof($non_null)) {
        echo implode(" - ", $non_null) . "\n";
      } else {
        print_r(func_get_args());
      }
    }
  }
}