<?php
    session_start();

    if ( !isset( $_GET["id"] ) ) {
        exit( "No id provided" );
    }

    if ( empty( $_GET["id"] ) ) {
        exit( "No id provided" );
    }

    $id = (int) $_GET["id"];

    if ( $id <= 0 ) {
        exit( "Invalid id provided" );
    }

    include( "db_connection.php" );

    if ( $stmt = $con->prepare( "SELECT `creator_id`, `title`, `content`, `expires`, `expiration_date`, `haspassword`, `password` FROM `pastes` WHERE `id` = ?" ) ) {
        $stmt->bind_param( "i", $id );
        $stmt->execute();
        $stmt->store_result();

        if ( $stmt->num_rows > 0 ) {
            $stmt->bind_result( $creator_id, $title, $content, $expires, $expiration_date, $haspassword, $password );
            $stmt->fetch();
            $stmt->close();

            if ( $stmt = $con->prepare( "SELECT `username` FROM `users` WHERE `id` = ?" ) ) {
                $stmt->bind_param( "i", $creator_id );
                $stmt->execute();
                $stmt->store_result();

                if ( $stmt->num_rows > 0 ) {
                    $stmt->bind_result( $username );
                    $stmt->fetch();
                } else {
                    $username = "<Unknown>";
                }

                $stmt->close();
            } else {
                $con->close();
                exit( "Couldn't prepare statement" );
            }

            if ( $haspassword ) {
                $con->close();
                header( "Location: view-password.php?id=" . urlencode( $id ) );
                exit();
            }

            if ( $expires && strtotime( $expiration_date ) < time() ) {
                if ( $stmt = $con->prepare( "DELETE FROM `pastes` WHERE `id` = ?" ) ) {
                    $stmt->bind_param( "i", $id );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $con->close();
                    exit( "Couldn't prepare statement" );
                }

                $con->close();
                exit( "This paste has expired." );
            }

            $con->close();
        } else {
            $stmt->close();
            $con->close();
            exit( "Paste not found" );
        }
    } else {
        $con->close();
        exit( "Statement couldn't be prepared" );
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Paste View</title>
</head>
<body>
    <?php
        echo "<h1 id=\"paste-title\">" . htmlspecialchars( $title ) . "</h1>";
        echo "<div id=\"paste-view-right\"><a href=\"/index.php\">Home</a></div>";

        if ( $username === "<Unknown>" ) {
            echo "<p id=\"paste-author\"> By: " . htmlspecialchars( $username ) . "</p>";
        } else {
            echo "<p id=\"paste-author\"> By: <a href=\"/profile.php?id=" . $creator_id . "\">" . htmlspecialchars( $username ) . "</a></p>";
        }

        echo "<pre id=\"paste-content\">" . htmlspecialchars( $content ) . "</pre>";
    ?>
</body>
</html>