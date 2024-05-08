<?php
session_start();
include_once '../Manage/user.php';

$user = new User($db);
$user->logout();
header('Location: ../../login.php');
exit();
?>
