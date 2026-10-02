<?php
    session_start();
    header( "Content-Type: application/json" );

    function invalid( $content ) {
        return json_encode( array( "title" => "Failed", "content" => $content ) );
        //echo( $content . ". <a href='register.php'>Return</a>" );
    }

    function success( $title, $content, $author, $authorID ) {
        return json_encode( array( "title" => $title, "content" => $content, "author" => $author, "author_id" => $authorID ) );
        //echo( $content . ". Login <a href='login.php'>here</a>");
    }

    if ( $_SERVER["REQUEST_METHOD"] !== "POST" ) {
        http_response_code( 405 );
        exit( invalid( "Invalid request method" ) );
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

    if ( $stmt = $con->prepare( "SELECT `title`, `content`, `creator_id`, `password`, `expires`, `expiration_date` FROM `pastes` WHERE `id` = ?" ) ) {
        $stmt->bind_param( "i", $id );
        $stmt->execute();
        $stmt->store_result();

        if ( $stmt->num_rows > 0 ) {
            $stmt->bind_result( $title, $content, $creatorID, $hash, $expires, $expiration_date );
            $stmt->fetch();

            if ( $expires && strtotime( $expiration_date ) < time() ) {
                if ( $stmt = $con->prepare( "DELETE FROM `pastes` WHERE `id` = ?" ) ) {
                    $stmt->bind_param( "i", $id );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $con->close();
                    exit( invalid( "Couldn't prepare statement" ) );
                }

                $con->close();
                exit( invalid( "This paste has expired." ) );
            }

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