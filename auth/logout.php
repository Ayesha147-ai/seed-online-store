<?php
session_start();
session_destroy();

// Cookie bhi hatao
setcookie('remember_token', '', time() - 3600, '/');

header('Location: ../login.html');
exit();
?>
