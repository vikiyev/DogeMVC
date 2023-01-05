<?php

class Users extends Controller {
  private $userModel;

  public function __construct() {
    $this->userModel = $this->model('User');
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
      } else {
        // check if email is taken
        if ($this->userModel->findUserByEmail($data['email'])) {
          $data['email_err'] = 'Email is already taken';
        }
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
        // hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        // register the user
        if ($this->userModel->register($data)) {
          // redirect to login page
          flash('register_success', 'You are now registered');
          redirect('users/login');
        } else {
          die('Something went wrong');
        }

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

      // check for user/email
      if ($this->userModel->findUserByEmail($data['email'])) {
        // user found
      } else {
        $data['email_err'] = 'No user found';
      }

      // make sure that there are no errors
      if (empty($data['email_err']) && 
        empty($data['password_err'])) {
        // valid form
        // check and set logged in user
        $loggedInUser = $this->userModel->login($data['email'], $data['password']);

        if ($loggedInUser) {
          // create the session
          $this->createUserSession($loggedInUser);
        } else {
          // rerender form with an error
          $data['password_err'] = 'Password incorrect';
          $this->view('users/login', $data);
        };
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

  public function createUserSession($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->name;
    redirect('pages/posts');
  }

  public function logout() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    session_destroy();
    redirect('users/login');
  }

}