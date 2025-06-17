<?php
    include("php\connection.php");
    $msg='';
    if(isset($_POST['submit'])){
      $name = $_POST['name'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $cpassword = $_POST['cpassword'];
      $user_type = $_POST['user_type'];

      $select1= "SELECT * FROM accounts WHERE email = '$email'";
      $select_user = mysqli_query($conn,$select1);
      if(mysqli_num_rows($select_user)> 0){
        $msg = "user already exist!";
      }elseif ($password != $cpassword) {
        $msg = "Passwords do not match!";
      }else{
        $insert1 = "INSERT INTO `accounts`(`name`, `email`, `password`, `user_type`) VALUES ('$name','$email','$password','$user_type')";
        mysqli_query($conn,$insert1);
        header('location:login.php');
      }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css\logreg.css">
  <title>Register Page</title>
</head>
<body>
  <div class="hero">
    <!-- Left Column -->
    <div class="left">
  <img src="image/logo.png" alt="Qupal University Logo" class="login-logo">
  <h2>Welcome!</h2>
  <p>Register Now</p>
</div>

    <!-- Right Column (Form) -->
    <div class="right">
      <form action="" method="post">
        <h2>Registration</h2>
        <p class="msg"><?php echo $msg; ?></p>
        
        <div class="form-group">
          <input type="text" name="name" placeholder="Enter your name" class="form-control" required>
        </div>
        <div class="form-group">
          <input type="email" name="email" placeholder="Enter your email" class="form-control" required>
        </div>
        <div class="form-group">
          <select name="user_type" class="form-control" style="display: none;">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Enter your password" class="form-control" required>
        </div>
        <div class="form-group">
          <input type="password" name="cpassword" placeholder="Confirm your password" class="form-control" required>
        </div>
        
        <button class="btn" name="submit">Register Now</button>
        <p style="margin-top: 10px;">Already have an account? <a href="login.php">Login Now</a></p>
      </form>
    </div>
  </div>
</body>
</html>