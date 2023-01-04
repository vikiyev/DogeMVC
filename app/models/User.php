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
}