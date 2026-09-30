<?php
    session_start();

    // echo var_dump( $_POST );

    function invalid( $content ) {
        return "<p style=\"color: red;\">" . $content . "</p>";
        //echo( $content . ". <a href='register.php'>Return</a>" );
    }

    function success( $content ) {
        return "<p style=\"color: green;\">" . $content . ". View on your profile <a href=\"/profile.php\">here</a></p>";
        //echo( $content . ". Login <a href='login.php'>here</a>");
    }

    function create() {
        // isset
        // array(5) { ["title"]=> string(0) "" ["content"]=> string(0) "" 
        //["visibility"]=> string(6) "public" ["date"]=> string(0) "" ["password"]=> string(0) "" }
        if ( !isset( $_POST["title"], $_POST["content"], $_POST["visibility"] ) ) {
            return invalid( "Missing data" );
        }

        // empty
        if ( empty( $_POST["title"] ) | empty( $_POST["content"] ) | empty( $_POST["visibility"] ) ) {
            return invalid( "Missing data" );
        }

        $creatorID = $_SESSION["userID"];
        $title = $_POST["title"];
        $content = $_POST["content"];
        $visibility = $_POST["visibility"];

        if ( $visibility != "public" && $visibility != "unlisted" ) {
            return invalid( "Invalid visibility" );
        }

        include( "db_connection.php" );

        if ( $stmt = $con->prepare( "SELECT `id` FROM `pastes` WHERE `title` = ? AND `creator_id` = ?" ) ) {
            $stmt->bind_param( "si", $title, $creatorID );
            $stmt->execute();
            $stmt->store_result();

            if ( $stmt->num_rows > 0 ) {
                $stmt->close();
                $con->close();

                return invalid( "You already have a paste with this title" );   
            }
        } else {
            $con->close();
            return invalid( "Statement couldn't be prepared" );
        }

        $hasPassword = isset( $_POST["haspassword"] ) && !empty( $_POST["haspassword"] ) && $_POST["haspassword"] == "on";
        $expires = isset( $_POST["expires"] ) && !empty( $_POST["expires"] ) && $_POST["expires"] == "on";

        if ( $hasPassword ) {
            // is password protected
            if ( !isset( $_POST["password"] ) ) {
                return invalid( "Missing data" );
            }

            if ( empty( $_POST["password"] ) ) {
                return invalid( "Missing data" );
            }

            $password = password_hash( $_POST["password"], PASSWORD_DEFAULT );

            if ( $expires ) {
                // has password and expires
                if ( !isset( $_POST["date"] ) ) {
                    return invalid( "Missing data" );
                }

                if ( empty( $_POST["date"] ) ) {
                    return invalid( "Missing data" );
                }
 
                $expirationDate = strtotime( $_POST["date"] );

                if ( !$expirationDate ) {
                    return invalid( "Invalid date" );
                }

                $expirationDate = date( "Y-m-d", $expirationDate );

            } else {
                // has password and doesn't expire
                $expirationDate = null;
            }

        } else {
            $password = null;

            if ( $expires ) {
                // no password and expires
                if ( !isset( $_POST["date"] ) ) {
                    return invalid( "Missing data" );
                }

                if ( empty( $_POST["date"] ) ) {
                    return invalid( "Missing data" );
                }

                $expirationDate = strtotime( $_POST["date"] );

                if ( !$expirationDate ) {
                    return invalid( "Invalid date" );
                }

                $expirationDate = date( "Y-m-d", $expirationDate );

            } else {
                // no password and doesn't expire
                $expirationDate = null;
                $password = null;
            }
        }

        if ( $stmt = $con->prepare( "INSERT INTO `pastes` (`creator_id`, `title`, `content`, `visibility`, `expires`, `expiration_date`, `haspassword`, `password`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)" ) ) {
            $stmt->bind_param( "isssisis", $creatorID, $title, $content, $visibility, $expires, $expirationDate, $hasPassword, $password );
            $stmt->execute();
            
            $stmt->close();
            $con->close();

            return success( "Paste has been created" );
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
    <title>Create</title>
</head>
<body>
    <?php
        if ( !isset( $_SESSION["isLoggedIn"] ) ) {
            exit("You must be logged in to create a paste. Login <a href=\"/login.php\">here</a>");
        }
    ?>
    <div id="create-right"><a href="/index.php">Home</a></div>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
        <label for="title">Title:</label>
        <input name="title" type="text" required>
        <br>
        <label for="content">Content:</label>
        <br>
        <textarea name="content" cols="50" rows="10" required></textarea>
        <br>
        <label for="visibility">Visibility: </label>
        <select name="visibility" required>
            <option value="public">Public</option>
            <option value="unlisted">Unlisted</option>
        </select>
        <br>
        <input name="expires" id="expires" type="checkbox">
        <label for="expires">Expiration Date</label>
        <br>
        <input name="date" id="expiration" type="date" min="<?php echo date( 'Y-m-d' ); ?>">
        <br>
        <input name="haspassword" id="haspassword" type="checkbox">
        <label for="haspassword">Password</label>
        <br>
        <input name="password" id="password" type="password" placeholder="Password">
        <?php if ( $_SERVER["REQUEST_METHOD"] == "POST" ) echo create();?>
        <button type="submit">Create</button>
    </form>
</body>
</html>