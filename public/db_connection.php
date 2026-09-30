<?php
    $db_host        = 'localhost';
    $db_username    = 'root';
    $db_password    = '';
    $db_name        = 'project';

    $con = mysqli_connect( $db_host, $db_username, $db_password, $db_name );

    if ( mysqli_connect_errno() ) {
        exit( "Failed to connect: " . mysqli_connect_error() );
    }
?>