<?php
    session_start();

    function invalid( $content ) {
        return "<p style=\"color: red;\">" . $content . "</p>";
    }

    function success( $content ) {
        return "<p style=\"color: green;\">" . $content . ". Go to the create page <a href=\"create.php\">here</a></p>";
    }

    function login() {
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
        if ( !preg_match( "/^[A-Za-z0-9_]*$/", $username ) ) {
            return invalid( "Username contains invalid characters" );
        }

        include( "db_connection.php" );

        if ( $stmt = $con->prepare( "SELECT `id`, `password` FROM `users` WHERE `username` = ?" ) ) {
            $stmt->bind_param( "s", $username );
            $stmt->execute();
            $stmt->store_result();

            if ( $stmt->num_rows > 0 ) {
                $stmt->bind_result( $id, $hash );
                $stmt->fetch();

                if ( password_verify( $password, $hash ) ) {
                    session_regenerate_id();

                    $_SESSION["isLoggedIn"] = true;
                    $_SESSION["username"] = $username;
                    $_SESSION["userID"] = $id;

                    $stmt->close();
                    $con->close();

                    return success( "You have successfully logged in" );
                } else {
                    $stmt->close();
                    $con->close();

                    return invalid( "Invalid password" );
                }

            } else {
                $stmt->close();
                $con->close();

                return invalid( "Username not found" );
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
    <title>Login</title>
</head>
<body>
    <?php
        if ( isset( $_SESSION["isLoggedIn"] ) ) {
            exit("You are already logged in! Go to the create page <a href=\"create.php\">here</a>");
        }
    ?>
    <form action="login.php" method="post">
        <label for="UserName">Username:</label>
        <input type="text" name="UserName" maxlength="10" pattern="[A-Za-z0-9_]+" title="Username can only contain letters, numbers and underscores" value="<?php echo isset( $_POST["UserName"] ) ? htmlspecialchars( $_POST["UserName"] ) : ""; ?>" required>
        <br>
        <label for="Password">Password:</label>
        <input type="password" name="Password" minlength="8" maxlength="16" required>
        <br>
        <?php if ( $_SERVER["REQUEST_METHOD"] == "POST" ) echo login();?>
        <p>Don't have an account? <a href="/register.php">Register</a></p>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>