<?php
require_once "auth_customer.php";
unset($_SESSION["customer"]);
header("Location: index.php");
exit;