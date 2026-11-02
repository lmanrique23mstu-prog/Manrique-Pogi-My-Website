<?php
session_start();
header('Location: ' . (empty($_SESSION['member']) ? 'signin.php' : 'home.php'));
exit;
