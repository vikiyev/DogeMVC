<?php

class Users extends Controller {


  public function __construct() {
    
  }

  public function register() {
    // check for request type
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // process the form submitted
    } else {
      // initialize data
      $data = [
        'name'=> '',
        'email'=> '',
        'password' => '',
        'confirm_password' => '',
        'name_err'=>'sasda',
        'email_err'=>'zxczxc',
        'password_err'=>'qweqwe',
        'confirm_password_err'=>'ayaya'
      ];
      
      // load view
      $this->view('users/register', $data);
    }
  }

  public function login() {
    // check for request type
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // process the form submitted
    } else {
      // initialize data
      $data = [
        'email'=> '',
        'password' => '',
        'email_err'=>'zxczxc',
        'password_err'=>'asdasdasd'
      ];
      
      // load view
      $this->view('users/login', $data);
    }
  }
}