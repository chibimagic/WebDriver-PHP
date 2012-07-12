<?php

class WebDriver_FirefoxProfile {
  protected $preferences = array();
  
  public function set_preference($key, $value) {
    $this->preferences[$key] = $value;
  }
  
  public function get_profile() {
    $tmp_filename = tempnam(sys_get_temp_dir(), "webdriver_firefox_profile_");
    
    $zip = new ZipArchive();
    $zip->open($tmp_filename, ZIPARCHIVE::CREATE);
    $zip->addFromString("prefs.js", $this->get_preferences_file());
    $zip->close();
    
    $base64 = base64_encode(file_get_contents($tmp_filename));
    unlink($tmp_filename);
    
    return $base64;
  }
  
  public function __toString() {
    return $this->get_profile;
  }
  
  protected function get_preferences_file() {
    $file = "";
    foreach ($this->preferences as $key => $value) {
      $file .= "user_pref(\"{$key}\", \"{$value}\");\n";
    }
    return $file;
  }
}
