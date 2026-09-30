<?php
    $config = require __DIR__ . "/../config.php";
    $db_host        = $config["host"];
    $db_username    = $config["username"];
    $db_password    = $config["password"];
    $db_name        = $config["database"];

    $con = mysqli_connect( $db_host, $db_username, $db_password, $db_name );

    if ( mysqli_connect_errno() ) {
        exit( "Failed to connect: " . mysqli_connect_error() );
    }
?>