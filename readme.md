# DogeMVC

Using query parameters, we can control which controllers and methods are loaded.

## MVC Pattern

Model

- Data related logic
- Interacts with the database
- Communicates with Controller
- Can sometimes update the view

View

- What the user sees in the browser
- Communicates with the controller
- Can be passed dynamic values from controller

Controller

- Receives input from the url, form, view etc.
- Processes requests
- Gets data from the model
- Passes data to the view

## Directing to index.php

We can setup an htaccess file under the root and public directory. The mod_rewrite module will be used for overriding the URL into index.php.

```htaccess
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteRule ^$ public/ [L]
  RewriteRule (.*) public/$1 [L]
</IfModule>
```

```htaccess
<IfModule mod_rewrite.c>
  Options -Multiviews
  RewriteEngine On
  RewriteBase /dogemvc/public
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule  ^(.+)$ index.php?url=$1 [QSA,L]
</IfModule>
```

## Boostrapping and Initializing the Core Class

The Core controller class can be initialized under `public/index.php`. In the Core class, we define a method for getting the URL.

```php
<?php
  require_once '../app/bootstrap.php';

  // initialize core library
  $init = new Core;
?>
```

```php
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
```

## Loading the Controller

To load the controller, we can check if the controller file exists and if it does, instantiate it.

```php
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
```

## Mapping Methods and Parameters

We can check if a method exists in a controller using **method_exists()**. The **call_user_func_array()** method calls a callback with an array of parameters

```php
    // Check for second URL segment (method)
    if (isset($url[1])) {
      // check to see if method exists for the controller
      if (method_exists($this->currentController, $url[1])) {
        $this->currentMethod = $url[1];
        unset($url[1]);
      }
    }

    // Get params
    $this->params = $url ? array_values($url) : [];
    call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
```

This now actually calls the parameter wherein we can access the parameters. We will see 33 being outputted when visiting `http://localhost/dogemvc/pages/about/33`.

```php
class Pages {
  public function about ($id) {
    echo 'About is loaded! <br>';
    echo $id;
  }
}
```

## Base Controller

The **Controller.php** class will be the base controller. All other controllers will extend this class.

```php
class Controller {
  // Load model
  public function model($model) {
    // Require the model file
    require_once '../app/models/' . $model . '.php';
    // Instantiate the model
    return new $model(); // ex: new Post;
  }

  // Load view
  public function view ($view, $data = []) {
    // Require the view file
    if (file_exists('../app/views/' . $view . '.php')) {
      require_once '../app/views/' . $view . '.php';
    } else {
      // view does not exist
      die('View does not exist');
    }
  }
}
```

We can then try loading the view in `views/pages/index.php` in our controller classes by extending the Controller class. We can also pass an optional argument from our controller to the view an array of parameters, which is accessible in the view using the $data property.

```php
class Pages extends Controller {
  public function index() {
    $this->view('pages/index', ['title' => 'Welcome!']);
  }
}
```

```php
<h1><?php echo $data['title'] ?></h1>
```

## Interacting with the Database

To interact with the Database, we use a PDO.

```php
  class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbhandler;
    private $stmt;
    private $error;

    public function __construct()
    {
      // Set DSN
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
      $options = array(
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      );

      // Create PDO instance
      try {
        $this->dbhandler = new PDO($dsn, $this->user, $this->pass, $options);
      } catch (PDOException $e) {
        $this->error = $e->getMessage();
        echo $this->error;
      }
    }

    // Prepare statement with query
    public function query($sql) {
      $this->stmt = $this->dbhandler->prepare($sql);
    }

    // Bind the values
    public function bind($param, $value, $type=null) {
      if (is_null($type)) {
        switch(true) {
          case is_int($value):
            $type = PDO::PARAM_INT;
            break;
          case is_bool($value):
            $type = PDO::PARAM_BOOL;
            break;
          case is_null($value):
            $type = PDO::PARAM_NULL;
            break;
          default:
            $type = PDO::PARAM_STR;
        }
      }

      $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute() {
      return $this->stmt->execute();
    }
  }
```

We can use a model to test the PDO.

```php
  class Post {
    private $db;

    public function __construct() {
      // Instantiate db
      $this->db = new Database;
    }

    public function getPosts() {
      $this->db->query("SELECT * FROM posts");
      $results = $this->db->resultSet();
      return $results;
    }
  }
```

We need to include the model in our controller class

```php
class Pages extends Controller {
  public function __construct() {
    $this->postModel = $this->model('Post');
  }

  public function index() {
    $posts = $this->postModel->getPosts();
    $data = [
      'title' => 'Welcome!',
      'posts' => $posts
  ];
    $this->view('pages/index', $data);
  }
}
```

We can then fetch the data from our index view

```php
<ul>
  <?php foreach($data['posts'] as $post) : ?>
    <li><?php echo $post->title; ?></li>
  <?php endforeach; ?>
</ul>
```

This way, we are able to load a model through our controller, call a model function to set a variable to be passed into the view.

## Authentication

For Authentication, we create a new controller for Users.php with methods for register and login. The method checks for the request type and performs validations and checks accordingly.

```php
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
}
```

We also create a User.php model file wherein we reach into the database using the Database.php library we created.

```php
class User {
  private $db;

  public function __construct() {
    // Instantiate db
    $this->db = new Database;
  }

  // find user by email
  public function findUserByEmail($email) {
    $this->db->query("SELECT * FROM users WHERE email = :email");
    // bind the value to the email
    $this->db->bind(':email', $email);

    // return the data
    $row = $this->db->single();
    // check the row if email exists
    if ($this->db->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }
}
```

We can then add the functionality for checking if an email already exists in the database on our controller class.

```php
      // validation
      if (empty($data['email'])) {
        $data['email_err'] = 'Please enter email';
      } else {
        // check if email is taken
        if ($this->userModel->findUserByEmail($data['email'])) {
          $data['email_err'] = 'Email is already taken';
        }
      }
```

## User Registration

To register, we need to create a new method in our model class

```php
  // register the user
  public function register($data) {
    // prepare the SQL statement
    $this->db->query("INSERT INTO users (name, email, password) VALUES(:name, :email, :password)");
    // bind values
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':email', $data['email']);
    $this->db->bind(':password', $data['password']);

    // execute the query
    if ($this->db->execute()) {
      return true;
    } else {
      return false;
    }
  }
```

In the controller, we can now call this method. We can also hash the password using **password_hash()**

```php
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
          redirect('users/login');
        } else {
          die('Something went wrong');
        }
```

## Flash Messaging

To display flash messages, we can create a helper function wherein we store session variables.

```php
<?php
  session_start();

  // flash message helper
  // example: flash('register_success', 'you are now registered', 'alert alert-danger');
  // display in view: <?php echo flash('register_success');
  function flash($name='', $message='', $class='alert alert-success') {
    if (!empty($name)) {
      // set session if message is currently not set inside a session
      if (!empty($message) && empty($_SESSION[$name])) {
        // unsets session if they exist
        if (!empty($_SESSION[$name])) {
          unset($_SESSION[$name]);
        }
        if (!empty($_SESSION[$name . '_class'])) {
          unset($_SESSION[$name . '_class']);
        }

        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
      } elseif(empty($message) && !empty($_SESSION[$name])) {
        // display message
        $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
        echo '<div class="'.$class.'" id="msg-flash">'.$_SESSION[$name].'</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
      }
    }
  }
```

We can now call the flash method in the Users controller

```php
flash('register_success', 'You are now registered');
```

And on our view

```php
<?php flash('register_success'); ?>
```

## Login

To login a user, we can create a new model method.

```php
  public function login($email, $password) {
    $this->db->query("SELECT * FROM users WHERE email = :email");
    $this->db->bind(':email', $email);

    $row = $this->db->single();
    $hashed_password = $row->password;

    // verify and match if password matches hashed password from db
    if (password_verify($password, $hashed_password)) {
      return $row;
    } else {
      return false;
    }
  }
```

We then have to verify in the controller if the user email exists and if there are no form errors before calling the model function for logging in.

```php
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
      }
```

We can then create the session

```php
  public function createUserSession($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->name;
    redirect('pages/index');
  }
```

## Logout

In the navbar, we can create a conditional for rendering the logout button based on the session.

```php
        <!-- login and register buttons -->
        <ul class="navbar-nav ml-auto mb-2 mb-lg-0">

          <?php if(isset($_SESSION['user_id'])) : ?>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="<?php echo URLROOT ?>/users/logout">Logout</a>
          </li>
          <?php else : ?>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="<?php echo URLROOT ?>/users/register">Register</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo URLROOT ?>/users/login">Login</a>
          </li>
          <?php endif; ?>
        </ul>
```

We need to add the logout method on our Users controller wherein we unset the session.

```php
  public function logout() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    session_destroy();
    redirect('users/login');
  }
```

## Posts Functionality

We first create a controller for the posts. We can also set up route guards to prevent users that are not logged in from accessing the posts endpoint. We can check if a session exists in the constructor and redirect if there are none.

```php
class Posts extends Controller {
  public function __construct()
  {
    if (!isLoggedIn()) {
      redirect('users/login');
    }
  }

  public function index() {
    $data = [];
    $this->view('posts/index', $data);
  }
}
```

We can use a helper function for checking if a user is logged in.

```php
  function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
      return true;
    } else {
      return false;
    }
  }
```

To fetch the posts, we need to create a post model and create a method to reach into the database.

```php
  class Post {
    private $db;

    public function __construct() {
      // Instantiate db
      $this->db = new Database;
    }

    public function getPosts() {
      $this->db->query("SELECT *,
                        posts.id as postId,
                        users.id as userId,
                        posts.created_at as postCreatedAt,
                        users.created_at as userCreatedAt
                        FROM posts INNER JOIN users ON posts.user_id = users.id
                        ORDER BY posts.created_at DESC");
      $results = $this->db->resultSet();
      return $results;
    }
  }
```

We can now load the model in our controller, and then use the model method to get the posts and initialize it into the $data variable.

```php
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
}
```

## Adding Posts

We create a new controller method for adding posts.

```php
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
          flash('post_added', 'Post has been added.');
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
```

We also need to create a corresponding method in the Post model.

```php
    public function addPost($data) {
      // prepare the SQL statement
      $this->db->query("INSERT INTO posts (title, user_id, body) VALUES(:title, :user_id, :body)");
      // bind values
      $this->db->bind(':title', $data['title']);
      $this->db->bind(':user_id', $data['user_id']);
      $this->db->bind(':body', $data['body']);

      // execute the query
      if ($this->db->execute()) {
        return true;
      } else {
        return false;
      }
    }
```

## Post Details

We can create a method in our Post controller that takes in the post id. We also neeed to create a model method for getting a single post.

```php
    public function getPostById($id) {
      // prepare the SQL statement
      $this->db->query("SELECT * FROM posts WHERE id = :id");
      // bind values
      $this->db->bind(':id', $id);

      $row = $this->db->single();
      return $row;
    }
```

```php
  public function show($id) {
    $post = $this->postModel->getPostById($id);
    $user = $this->userModel->getUserById($post->user_id);

    $data = [
      'post' => $post,
      'user' => $user
    ];
    $this->view('posts/show', $data);
  }
```

## Edit and Delete

To conditionally render the edit button, we can compare the user_id from the session user_id.

```php
<?php if($data['post']->user_id == $_SESSION['user_id']) : ?>
  <hr>
  <a href="<?php echo URLROOT ?>/posts/edit<?php echo $data['post']->id ?>" class="btn btn-dark">Edit</a>

  <form action="<?php echo URLROOT ?>/posts/delete/<?php echo $data['post']->id ?>" method="POST">
    <input type="submit" value="Delete" class="btn btn-danger pull-right">
  </form>
<?php endif; ?>
```

We then create the controller method.

```php
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
        if ($this->postModel->udpatePost($data)) {
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
```

And then the model method for updatePost.

```php
    public function updatePost($data) {
      $this->db->query("UPDATE posts SET title = :title, body = :body WHERE id = :id");
      $this->db->bind(':title', $data['title']);
      $this->db->bind(':body', $data['body']);
      $this->db->bind(':id', $data['id']);

      if ($this->db->execute()) {
        return true;
      } else {
        return false;
      }
    }
```
