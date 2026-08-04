<?php
include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name  = $_POST['name'];
  $email = $_POST['email'];
  $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role  = $_POST['role']; 

  $query = "INSERT INTO users (name,email,password,role)
            VALUES ('$name','$email','$pass','$role')";

  if (mysqli_query($conn, $query)) {
    echo "Signup successful";
  } else {
    echo "Email already exists";
  }
}
