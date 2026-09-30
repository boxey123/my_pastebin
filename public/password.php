<?php
    session_start();

    function invalid( $content ) {
        return json_encode( array( "title" => "Failed", "content" => $content ) );
        //echo( $content . ". <a href='register.php'>Return</a>" );
    }

    function success( $title, $content, $author, $authorID ) {
        return json_encode( array( "title" => $title, "content" => $content, "author" => $author, "author_id" => $authorID ) );
        //echo( $content . ". Login <a href='login.php'>here</a>");
    }

    if ( !isset( $_POST["id"], $_POST["password"] ) ) {
        exit( invalid( "Missing data" ) );
    }

    if ( empty( $_POST["id"] ) || empty( $_POST["password"] ) ) {
        exit( invalid( "Missing data" ) );
    }

    $id = (int) $_POST["id"];
    $password = $_POST["password"];

    if ( $id <= 0 ) {
        exit( invalid( "Invalid id provided" ) );
    }

    include( "db_connection.php" );

    if ( $stmt = $con->prepare( "SELECT `title`, `content`, `creator_id`, `password` FROM `pastes` WHERE `id` = ?" ) ) {
        $stmt->bind_param( "i", $id );
        $stmt->execute();
        $stmt->store_result();

        if ( $stmt->num_rows > 0 ) {
            $stmt->bind_result( $title, $content, $creatorID, $hash );
            $stmt->fetch();

            if ( password_verify( $password, $hash ) ) {
                // get author name
                if ( $stmt = $con->prepare( "SELECT `username` FROM `users` WHERE `id` = ?" ) ) {
                    $stmt->bind_param( "i", $creatorID );
                    $stmt->execute();
                    $stmt->store_result();

                    if ( $stmt->num_rows > 0 ) {
                        $stmt->bind_result( $author );
                        $stmt->fetch();

                        echo success( $title, $content, $author, $creatorID );

                    } else {
                        echo invalid( "Author not found" );
                    }

                } else {
                    echo invalid( "Couldn't prepare statement" );
                }

            } else {
                echo invalid( "Incorrect password" );
            }
        } else {
            echo invalid( "Paste not found" );
        }
    } else {

        echo invalid( "Couldn't prepare statement" );
    }
?>