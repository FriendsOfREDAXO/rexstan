<?php

function() {
    $_SESSION['foo'] = 'bar';
    $x = $_GET['foo'];
    $x = $_POST['foo'];
    $x = $_REQUEST['foo'];
    $x = $_COOKIE['foo'];
    $x = $_SERVER['foo'];
    $x = $_ENV['foo'];
};
