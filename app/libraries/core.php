<?php
/*
* App Core Class
* Creates URL and loads core controller
* URL FORMAT - /controller/method/params
*/

class Core {
  protected $currentController = 'Pages';
  protected $currentMethod = 'index';
  protected $params = [];

  public function __construct(){
    print_r($this->getURL());
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