<?php

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

  public function getUserById($id) {
    $this->db->query("SELECT * FROM users WHERE id = :id");
    $this->db->bind(':id', $id);
    $row = $this->db->single();
    return $row;
  }

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
}