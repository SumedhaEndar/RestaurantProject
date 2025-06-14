<?php
require_once '../config.php';
session_start();

$email = $password = "";
$email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT * FROM Accounts WHERE email = ?";

        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row["password"]) || $password === $row["password"]) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["email"] = $email;
                    $_SESSION["account_id"] = $row['account_id'];

                    // Get membership info
                    $sql_member = "SELECT member_name, points FROM Memberships WHERE account_id = ?";
                    if ($stmt_member = $link->prepare($sql_member)) {
                        $stmt_member->bind_param("i", $row['account_id']);
                        $stmt_member->execute();
                        $result_member = $stmt_member->get_result();

                        if ($membership_row = $result_member->fetch_assoc()) {
                            $_SESSION["member_name"] = $membership_row["member_name"];
                            $_SESSION["points"] = $membership_row["points"];
                        } else {
                            $_SESSION["member_name"] = "Member";
                            $_SESSION["points"] = 0;
                        }
                        $stmt_member->close();
                    } else {
                        $_SESSION["member_name"] = "Member";
                        $_SESSION["points"] = 0;
                    }

                    header("Location: ../home/dashboard.php");
                    exit();
                } else {
                    $password_err = "Invalid password.";
                }
            } else {
                $email_err = "No account found with this email.";
            }
            $stmt->close();
        }
    }
}
?>

<!-- Your existing HTML form here -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        

/* Style for the container within login.php */
.login-container {
  padding: 50px; /* Adjust the padding as needed */
  border-radius: 10px; /* Add rounded corners */
  margin: 100px auto; /* Center the container horizontally */
  max-width: 500px; /* Set a maximum width for the container */
}


        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0; /* Remove default margin */
            background-color:black;
             background-image: url('../image/loginBackground.jpg'); /* Set the background image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
        }

        .login_wrapper {
            width: 400px; /* Adjust the container width as needed */
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-family: 'Montserrat', serif;
        }

        p {
            font-family: 'Montserrat', serif;
        }

        .form-group {
            margin-bottom: 15px; /* Add space between form elements */
        }

        ::placeholder {
            font-size: 12px; /* Adjust the font size as needed */
        }
        
        .text-danger{
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
    <div class="login_wrapper">
        <a class="nav-link" href="../home/home.php#hero"> <h1 class="text-center" style="font-family:Copperplate; color:white;"> JOHNNY'S</h1><span class="sr-only"></span></a>
    
        <div class="wrapper">
           
        <form action="login.php" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter User Email" required>
                <span class="text-danger"><?php echo $email_err; ?></span>
            </div>

           <div class="form-group">
    <label>Password</label>
    <input type="password" name="password" class="form-control" placeholder="Enter User Password" required>
    <span class="text-danger"><?php echo $password_err; ?></span>
</div>
            <button class="btn btn-dark" style="background-color:black;" type="submit" name="submit" value="Login">Login</button>
            
        </form>

            <p style="margin-top:1em; color:white;">Don't have an account? <a href="register.php" style="">Proceed to Register</a></p>
        </div>
    </div>
    </div>
</body>
</html>

