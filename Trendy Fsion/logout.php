<?php
include("login1.php");
session_start();
session_destroy();
header("location:login-form.php");


?>