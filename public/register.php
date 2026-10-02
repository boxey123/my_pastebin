<?php
    session_start();

    function invalid( $content ) {
        return "<p style=\"color: red;\">" . $content . "</p>";
    }

    function success( $content ) {
        return "<p style=\"color: green;\">" . $content . ". Login <a href=\"login.php\">here</a></p>";
    }

    function register() {
        // isset
        if ( !isset( $_POST["UserName"], $_POST["Password"] ) ) {
            return invalid( "Missing data" );
        }

        // empty
        if ( empty( $_POST["UserName"] ) || empty( $_POST["Password"] ) ) {
            return invalid( "Missing data" );
        }

        $username = $_POST["UserName"];
        $password = $_POST["Password"];

        // length
        if ( strlen( $username ) > 10 ) {
            return invalid( "Username is too long" );
        }

        if ( strlen( $password ) < 8 || strlen( $password ) > 16 ) {
            return invalid( "Password is too short/long" );
        }

        // characters
        if ( !preg_match( "/^[A-Za-z0-9_]*\z/", $username ) ) {
            return invalid( "Username contains invalid characters" );
        }

        include( "db_connection.php" );
        if ( $stmt = $con->prepare( "SELECT `id` FROM `users` WHERE `username` = ?" ) ) {
            $stmt->bind_param( "s", $username );
            $stmt->execute();
            $stmt->store_result();
            if ( $stmt->num_rows > 0 ) {
                $stmt->close();
                $con->close();
                return invalid( "A user with this username already exists" );
            } else { 
                $timestamp = date( "Y-m-d H:i:s" );
                $password = password_hash( $password, PASSWORD_DEFAULT );

                if ( $stmt->prepare( "INSERT INTO `users` (`timestamp`, `username`, `password`) VALUES (?, ?, ?)" ) ) {
                    $stmt->bind_param( "sss", $timestamp, $username, $password );
                    $stmt->execute();
                    $stmt->close();
                    $con->close();
                    return success( "You have successfully registered" );
                } else {
                    $stmt->close();
                    $con->close();
                    return invalid( "Statement couldn't be prepared" );
                }
            }
        } else {
            $con->close();
            return invalid( "Statement couldn't be prepared" );
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
</head>
<body>
    <?php
        if ( isset( $_SESSION["isLoggedIn"] ) ) {
            exit("You are already logged in! Go to the create page <a href=\"create.php\">here</a>");
        }
    ?>
    <form action="register.php" method="post">
        <label for="UserName">Username:</label>
        <input type="text" name="UserName" maxlength="10" pattern="[A-Za-z0-9_]+" title="Username can only contain letters, numbers and underscores" value="<?php echo isset( $_POST["UserName"] ) ? htmlspecialchars( $_POST["UserName"] ) : ""; ?>" required>
        <br>
        <label for="Password">Password:</label>
        <input type="password" name="Password" minlength="8" maxlength="16" required>
        <br>
        <?php if ( $_SERVER["REQUEST_METHOD"] == "POST" ) echo register();?>
        <p>Already have an account? <a href="/login.php">Login</a></p>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>