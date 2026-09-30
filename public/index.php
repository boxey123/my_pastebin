<?php
    session_start();

    function display_pastes() {
        include( "db_connection.php" );

        if ( $stmt = $con->prepare( "SELECT `id`, `title`, `creator_id` FROM `pastes` WHERE `visibility` = 'public' ORDER BY `id` DESC LIMIT 5" ) ) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result( $id, $title, $creator_id );

            if ( $stmt->num_rows > 0 ) {
                while ( $stmt->fetch() ) {
                    if ( $stmt2 = $con->prepare( "SELECT `username` FROM `users` WHERE `id` = ?" ) ) {
                        $stmt2->bind_param( "s", $creator_id );
                        $stmt2->execute();
                        $stmt2->store_result();

                        if ( $stmt2->num_rows > 0 ) {
                            $stmt2->bind_result( $username );
                            $stmt2->fetch();
                        } else {
                            $stmt2->close();
                            $con->close();
                            exit( "Creator not found" );
                        }
                        echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars( $title ) . "</a> by <a href=\"/profile.php?id=" . $creator_id . "\">" . htmlspecialchars( $username ) . "</a><br>";

                        $stmt2->close();
                    } else {
                        $stmt->close();
                        $con->close();
                        exit( "Statement 2 couldn't be prepared" );
                    }
                }
            } else {
                echo "No public pastes found.";
            }
        } else {
            $con->close();
            exit( "Statement couldn't be prepared" );
        }

        $stmt->close();
        $con->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Home</title>
</head>
<header id="index-header">
    <?php
        if ( isset( $_SESSION["isLoggedIn"] ) ) {
            // display profile link on the left and create on the right (float left, right)
            echo "<a id=\"profile-link\" href=\"/profile.php\">Profile</a>";
            echo "<a id=\"create-link\" href=\"/create.php\">Create</a>";
        } else {
            // display login button on the right (float right)
            echo "<a id=\"login-link\" href=\"/login.php\">Login</a>";
        }
    ?>
    <!-- this isn't properly centred -->
    <h1 id="index-title">My pastebin</h1>
</header>
<body>
    <div id="index-div">
        <section></section>
        <section><h3>Recent Public Pastes</h3><?php display_pastes(); ?></section>
        <section></section>
    </div>
</body>
</html>