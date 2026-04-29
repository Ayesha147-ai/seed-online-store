<?php
$success = 0;
$user = 0;
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    include 'connect.php';
    
    $fullname  = $_POST['fullname'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if($password !== $confirm){
        $error = "Passwords do not match!";
    } else {
        $sql = "SELECT * FROM registration WHERE email='$email'";
        $result = mysqli_query($con, $sql);
        $num = mysqli_num_rows($result);

        if($num > 0){
            $user = 1;
        } else {
            $sql = "INSERT INTO registration (fullname, email, phone, password) 
                    VALUES ('$fullname', '$email', '$phone', '$password')";
            $result = mysqli_query($con, $sql);
            if($result){
                $success = 1;
            } else {
                die(mysqli_error($con));
            }
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TrackSeeds</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css-f/signup.css">
</head>

<body>

    <!-- Signup Section -->
    <section class="signup-section">
        <div class="signup-box">

            <!-- Left Side Image -->
            <div class="signup-image">
                <img src="css-f/img/flow(page).jpg" alt="Seeds">
                <div class="overlay">
                    <h2>Join TrackSeeds!</h2>
                    <p>Create your account and start tracking your seeds today</p>
                </div>
            </div>

            <!-- Right Side Form -->
            <div class="signup-form">
                <form action="signup.php" method="POST">
                <h3>Create Your Account</h3>
                <p>Enter your details below</p>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Full Name" name="fullname">
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email Address" name="email">
                </div>

                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" placeholder="Phone Number" name="phone">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" name="password">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Confirm Password" name="confirm_password">
                </div>

                <div class="form-options">
                    <label>
                        <input type="checkbox"> I agree to 
                        <a href="#">Terms & Conditions</a>
                    </label>
                </div>

                <button class="signup-submit-btn">Sign Up</button>

                <p class="switch-text">
                    Already have an account?
                    <a href="login.html">Login</a>
                </p>

                <p class="back-home">
                    <a href="index.html">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </p>



</form>
            </div>
        </div>
    </section>

</body>
</html>