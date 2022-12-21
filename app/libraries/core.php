<?php
/*
* App Core Class
* Creates URL and loads core controller
* URL FORMAT - /controller/method/params
*/

class Core {
  protected $currentController = 'Pages'; // default to Pages controller
  protected $currentMethod = 'index';
  protected $params = [];

  public function __construct(){
    // print_r($this->getURL());
    $url = $this->getURL();

    // look in controllers for first URL segment
    if (isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . '.php')) {
      // if file exists, set it as the controller
      $this->currentController = ucwords($url[0]);
      // unset 0 index
      unset($url[0]);
    }

    // Require the controller
    require_once '../app/controllers/' . $this->currentController . '.php';

    // Instantiate the controller class
    $this->currentController = new $this->currentController;
  }

  public function getURL() {
    if (isset($_GET['url'])) {
      $url = rtrim($_GET['url'], '/');
      $url = filter_var($url, FILTER_SANITIZE_URL);
      // break the url into array
      $url = explode('/', $url);
      return $url;
    }
  }
}