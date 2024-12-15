<?php
session_start();
session_destroy();

include_once 'config.php';

header("Location: " . BASE_URL . "/index.php");
exit();
?>
