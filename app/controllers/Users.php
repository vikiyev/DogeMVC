<?php

class Users extends Controller {


  public function __construct() {
    
  }

  public function register() {
    // check for request type
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // process the form submitted
      // sanitize POST data
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      // initialize data
      $data = [
        'name'=> trim($_POST['name']),
        'email'=> trim($_POST['email']),
        'password' => trim($_POST['password']),
        'confirm_password' => trim($_POST['confirm_password']),
        'name_err'=>'',
        'email_err'=>'',
        'password_err'=>'',
        'confirm_password_err'=>''
      ];

      // validation
      if (empty($data['email'])) {
        $data['email_err'] = 'Please enter email';
      }

      if (empty($data['name'])) {
        $data['name_err'] = 'Please enter name';
      }

      if (empty($data['password'])) {
        $data['password_err'] = 'Please enter password';
      } elseif(strlen($data['password']) < 6) {
        $data['password_err'] = 'Password must be at least 6 characters';
      }

      if (empty($data['confirm_password'])) {
        $data['confirm_password_err'] = 'Please confirm password';
      } else {
        if ($data['password'] != $data['confirm_password']) {
          $data['confirm_password_err'] = 'Passwords do not match';
        }
      }

      // make sure that there are no errors
      if (empty($data['email_err']) && 
        empty($data['name_err']) &&
        empty($data['password_err']) &&
        empty($data['confirm_password_err'])) {
        // valid form
        die('SUCCESS');
      } else {
        // reload the view with errors
        $this->view('users/register', $data);
      }

    } else {
      // GET request
      // initialize data
      $data = [
        'name'=> '',
        'email'=> '',
        'password' => '',
        'confirm_password' => '',
        'name_err'=>'',
        'email_err'=>'',
        'password_err'=>'',
        'confirm_password_err'=>''
      ];
      
      // load view
      $this->view('users/register', $data);
    }
  }

  public function login() {
    // check for request type
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // process the form submitted
      // sanitize POST data
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);        
      
      // initialize data
      $data = [
        'email'=> trim($_POST['email']),
        'password' => trim($_POST['password']),
        'email_err'=>'',
        'password_err'=>'',
      ];

      // validation
      if (empty($data['email'])) {
        $data['email_err'] = 'Please enter email';
      }

      if (empty($data['password'])) {
        $data['password_err'] = 'Please enter password';
      } elseif(strlen($data['password']) < 6) {
        $data['password_err'] = 'Password must be at least 6 characters';
      }

      // make sure that there are no errors
      if (empty($data['email_err']) && 
        empty($data['password_err'])) {
        // valid form
        die('SUCCESS');
      } else {
        // reload the view with errors
        $this->view('users/login', $data);
      }

    } else {
      // initialize data
      $data = [
        'email'=> '',
        'password' => '',
        'email_err'=>'',
        'password_err'=>''
      ];
      
      // load view
      $this->view('users/login', $data);
    }
  }
}