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
