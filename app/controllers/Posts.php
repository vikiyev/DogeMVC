<?php

class Posts extends Controller {
  private $postModel;
  private $userModel;

  public function __construct() {
    if (!isLoggedIn()) {
      redirect('users/login');
    }

    $this->postModel = $this->model('Post');
    $this->userModel = $this->model('User');
  }

  public function index() {
    $posts = $this->postModel->getPosts();
    $data = [
      'posts'=> $posts
    ];
    $this->view('posts/index', $data);
  }

  // controller for specific post
  // posts/show/:id
  public function show($id) {
    $post = $this->postModel->getPostById($id);
    $user = $this->userModel->getUserById($post->user_id);

    $data = [
      'post' => $post,
      'user' => $user
    ];
    $this->view('posts/show', $data);
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

  public function edit($id) {
    // check request if POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // sanitize POST array
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $data = [
        'id' => $id,
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
        if ($this->postModel->updatePost($data)) {
          flash('post_message', 'Post has been updated.');
          redirect('posts');
        } else {
          die('Something went wrong');
        }

      } else {
        // rerender view with errors
        $this->view('posts/edit', $data);
      }

    } else {
      // fetch the post
      $post = $this->postModel->getPostById($id);

      // check if user is the owner
      if ($post->user_id != $_SESSION['user_id']) {
        redirect('posts');
      }

      $data = [
        'id' => $id,
        'title'=> $post->title,
        'body'=> $post->body
      ];
      $this->view('posts/edit', $data);
    }
  }

  public function delete($id) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // fetch the post
      $post = $this->postModel->getPostById($id);
            
      // check if user is the owner
      if ($post->user_id != $_SESSION['user_id']) {
        redirect('posts');
      }

      if ($this->postModel->deletePost($id)) {
        flash('post_message', 'Post deleted');
        redirect('posts');  
      } else {
        die("Something went wrong");
      }
    } else {
      redirect('posts');
    }
  }
}