<?php

class Pages extends Controller {
  private $postModel; // delete if bugged

  public function __construct() {
    // $this->postModel = $this->model('Post');
  }

  public function index() {
    if (isLoggedIn()) {
      redirect('posts');
    }


    $data = [
      'title' => 'DogeMVC', 
      'description' => 'Wow, such Doge! Much PHP. Very MVC.'
      // 'posts' => $posts
  ];
    $this->view('pages/index', $data);
  }

  public function about (){
    $data = [
      'title' => 'About Us',
      'description' => 'An app for sharing posts with other doges'
    ];
    $this->view('pages/about', $data);
  }
}