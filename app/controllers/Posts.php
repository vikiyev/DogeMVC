<?php

class Posts extends Controller {
  private $postModel;

  public function __construct() {
    if (!isLoggedIn()) {
      redirect('users/login');
    }

    $this->postModel = $this->model('Post');
  }

  public function index() {
    $posts = $this->postModel->getPosts();
    $data = [
      'posts'=> $posts
    ];
    $this->view('posts/index', $data);
  }

  public function add() {
    // check request if POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // sanitize POST array
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $data = [
        'title'=> trim($_POST['title']),
        'body'=> trim($_POST['body']),
        'user_id'=> $_SESSION['user_id'],
        'title_err'=> '',
        'body_err'=> ''
      ];

      // validation
      if(empty($data['title'])) {
        $data['title_err'] = 'Please enter a title';
      }

      if(empty($data['body'])) {
        $data['body_err'] = 'Please enter body text';
      }

      // make sure there are no errors
      if (empty($data['title_err']) && empty($data['body_err'])) {
        // validated
        if ($this->postModel->addPost($data)) {
          flash('post_message', 'Post has been added.');
          redirect('posts');
        } else {
          die('Something went wrong');
        }

      } else {
        // rerender view with errors
        $this->view('posts/add', $data);
      }

    } else {
      $data = [
        'title'=> '',
        'body'=> ''
      ];
      $this->view('posts/add', $data);
    }
  }
}