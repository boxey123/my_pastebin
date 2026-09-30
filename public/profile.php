<?php
    session_start();

    include( "db_connection.php" );

    $found = false;
    $viewType;

    if ( isset( $_GET["id"] ) ) {
        if ( $stmt = $con->prepare( "SELECT `username` FROM `users` WHERE `id` = ?" ) ) {
            $stmt->bind_param( "s", $_GET["id"] );
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result( $username );
            $stmt->fetch();

            if ( $stmt->num_rows > 0 ) {
                $found = true;
                $viewType = "Public Profile";
            }
        } else {
            $con->close();
            exit( "Couldn't prepare statement" );
        }

        $stmt->close();
    } else {
        if ( !isset( $_SESSION["isLoggedIn"] ) ) {
            exit( "You aren't logged in. Log in <a href=\"/login.php\">here</a>" );
        }
        
        $username = $_SESSION["username"];
        $found = true;
        $viewType = "Private Profile";
    }

    if ( !$found ) {
        exit( "User not found." );
    } 

    $con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo htmlspecialchars( $username ) . " | " . $viewType; ?></title>
</head>
<body>
    <div>
        <h1 id="profile-username">User: <?php echo htmlspecialchars( $username ); ?></h1>
        <?php
            if ( $viewType == "Private Profile" ) {
                echo "<div id=\"profile-right\"><a href=\"/index.php\">Home</a><a href=\"/logout.php\">Logout</a></div>";
            } else {
                echo "<div id=\"profile-right\"><a href=\"/index.php\">Home</a></div>";
            }
        ?>
    </div>
    <div> <!-- user creations -->
        <h3>User's Creations:</h3>
        <div>
            <?php
                include( "db_connection.php" );

                if ( $viewType == "Public Profile" ) {
                    if ( $stmt = $con->prepare( "SELECT `id`, `title` FROM `pastes` WHERE `creator_id` = ? AND `visibility` = 'public'" ) ) {
                        $stmt->bind_param( "s", $_GET["id"] );
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result( $id, $title );

                        while ( $stmt->fetch() ) {
                            echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars( $title ) . "</a><br>";
                        }
                    } else {
                        $stmt->close();
                        $con->close();
                        exit( "Couldn't prepare statement" );
                    }
                } else {
                    if ( $stmt = $con->prepare( "SELECT `id`, `title` FROM `pastes` WHERE `creator_id` = ?" ) ) {
                        $stmt->bind_param( "s", $_SESSION["userID"] );
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result( $id, $title );

                        while ( $stmt->fetch() ) {
                            echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars( $title ) . "</a><br>";
                        }
                    } else {
                        $stmt->close();
                        $con->close();
                        exit( "Couldn't prepare statement" );
                    }
                }
            ?>
        </div>
    </div>
</body>
</html>