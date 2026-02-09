<?php
session_start();

// Destroy session
session_destroy();

// Redirect to main page
header('Location: index.html');
exit();
?>
