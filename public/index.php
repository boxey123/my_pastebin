<?php
session_start();

function display_pastes()
{
    $con = require("db_connection.php");

    $sql = <<<'SQL'
        SELECT `id`, `title`, `creator_id` FROM `pastes`
        WHERE `visibility` = 'public' AND ( `expires` = 0 OR `expiration_date` > CURDATE() )
        ORDER BY `id` DESC LIMIT 5
    SQL;

    if ($stmt = $con->prepare($sql)) {
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $title = $row['title'];
                $creator_id = $row['creator_id'];

                $sql = <<<'SQL'
                    SELECT `username` FROM `users`
                    WHERE `id` = ?;
                SQL;

                if ($stmt2 = $con->prepare($sql)) {
                    $stmt2->bind_param("i", $creator_id);
                    $stmt2->execute();

                    $result2 = $stmt2->get_result();

                    if ($result2->num_rows > 0) {
                        $row2 = $result2->fetch_assoc();
                        $username = $row2['username'];
                    } else {
                        $username = "<Unknown>";
                    }

                    if ($username === "<Unknown>") {
                        echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars($title) .
                            "</a> by <span>" . htmlspecialchars($username) . "</span><br>";
                    } else {
                        echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars($title) .
                            "</a> by <a href=\"/profile.php?id=" .
                            $creator_id . "\">" . htmlspecialchars($username) . "</a><br>";
                    }

                    $stmt2->close();
                } else {
                    $stmt->close();
                    $con->close();
                    exit("Statement 2 couldn't be prepared");
                }
            }
        } else {
            echo "No public pastes found";
        }

        $stmt->close();
    } else {
        $con->close();
        exit("Statement couldn't be prepared");
    }

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
    if (isset($_SESSION["isLoggedIn"])) {
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
        <section>
            <h3>Recent Public Pastes</h3><?php display_pastes(); ?>
        </section>
        <section></section>
    </div>
</body>

</html>