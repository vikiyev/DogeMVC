<?php

class Pages {
  public function __construct() {
    
  }

  public function index() {
    echo 'Index';
  }

  public function about ($id){
    echo 'About is loaded! <br>';
    echo $id;
  }
}