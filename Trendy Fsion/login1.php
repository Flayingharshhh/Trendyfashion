<?php
$hostname = "localhost";
$username = "root";
$password = "";
$databasename = "codingstatus";

// Create connection
$conn = new mysqli($hostname, $username, $password, $databasename);

// Check connection
if ($conn->connect_error) {
    die("Unable to connect to the database: " . $conn->connect_error);
}
?>

<?php
// Include file which makes the database connection.
include 'SignUp.php';

$showAlert = false;
$showError = false;
$exists = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);

    if ($num == 0) {
        if (($password == $cpassword) && $exists == false) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, date) VALUES ('$username', '$hash', current_timestamp())";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $showAlert = true;
            }
        } else {
            $showError = "Passwords do not match";
        }
    } elseif ($num > 0) {
        $exists = "Username not available";
    }
}
?>

<?php
require_once('database.php');
$db = $conn; // Update with your database connection
$register = $valid = $fnameErr = $lnameErr = $emailErr = $passErr = $cpassErr = '';
$set_firstName = $set_lastName = $set_email = '';

extract($_POST);
if (isset($_POST['submit'])) {
    // Input fields are validated with regular expressions
    $validName = "/^[a-zA-Z ]*$/";
    $validEmail = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/";
    $uppercasePassword = "/(?=.*?[A-Z])/";
    $lowercasePassword = "/(?=.*?[a-z])/";
    $digitPassword = "/(?=.*?[0-9])/";
    $spacesPassword = "/^$|\s+/";
    $symbolPassword = "/(?=.*?[#?!@$%^&*-])/";
    $minEightPassword = "/.{8,}/";

    // First Name Validation
    if (empty($first_name)) {
        $fnameErr = "First Name is required";
    } else if (!preg_match($validName, $first_name)) {
        $fnameErr = "Digits are not allowed";
    } else {
        $fnameErr = true;
    }

    // Last Name Validation
    if (empty($last_name)) {
        $lnameErr = "Last Name is required";
    } else if (!preg_match($validName, $last_name)) {
        $lnameErr = "Digits are not allowed";
    } else {
        $lnameErr = true;
    }

    // Email Address Validation
    if (empty($email)) {
        $emailErr = "Email is required";
    } else if (!preg_match($validEmail, $email)) {
        $emailErr = "Invalid Email Address";
    } else {
        $emailErr = true;
    }
    if (empty($password)) {
        $passErr = "Password is required";
    } elseif (!preg_match($uppercasePassword, $password) || !preg_match($lowercasePassword, $password) || !preg_match($digitPassword, $password) || !preg_match($symbolPassword, $password) || !preg_match($minEightPassword, $password) || preg_match($spacesPassword, $password)) {
        $passErr = "Password must contain at least one uppercase letter, one lowercase letter, one digit, one special character, no spaces, and be at least 8 characters long";
    } else {
        $passErr = true;
    }

    // Form validation for confirm password
    if ($cpassword != $password) {
        $cpassErr = "Confirm Password does not match";
    } else {
        $cpassErr = true;
    }

    // Check if all fields are valid
    if ($fnameErr === true && $lnameErr === true && $emailErr === true && $passErr === true && $cpassErr === true) {
        $firstName = legal_input($first_name);
        $lastName = legal_input($last_name);
        $email = legal_input($email);
        $password = legal_input(md5($password));

        // Check unique email
        $checkEmail = unique_email($email);
        if ($checkEmail) {
            $register = $email . " is already taken";
        } else {
            // Insert data
            $register = register($firstName, $lastName, $email, $password);
        }
    } else {
        // Set input values empty until input field is invalid
        $set_firstName = $first_name;
        $set_lastName = $last_name;
        $set_email = $email;
    }
}

// Convert illegal input value to a legal value format
function legal_input($value)
{
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value);
    return $value;
}

// Function to check if the email is already present in the database
function unique_email($email)
{
    global $db;
    $sql = "SELECT email FROM users WHERE email='" . $email . "'";
    $check = $db->query($sql);

    if ($check->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

// Function to insert user data into the database table
function register($firstName, $lastName, $email, $password)
{
    global $db;
    $sql = "INSERT INTO users(first_name, last_name, email, password) VALUES(?, ?, ?, ?)";
    $query = $db->prepare($sql);
    $query->bind_param('ssss', $firstName, $lastName, $email, $password);
    $exec = $query->execute();

    if ($exec) {
        return "You have been registered successfully";
    } else {
        return "Error: " . $sql . "<br>" . $db->error;
    }
}
?>
