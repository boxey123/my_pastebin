<?php
session_start();

if (!isset($_GET["id"])) {
    exit("No id provided");
}

if (empty($_GET["id"])) {
    exit("No id provided");
}

$id = (int) $_GET["id"];

if ($id <= 0) {
    exit("Invalid id provided");
}

$con = require("db_connection.php");

$sql = <<<'SQL'
    SELECT `creator_id`, `title`, `content`, `expires`, `expiration_date`, `haspassword`, `password`
    FROM `pastes`
    WHERE `id` = ?
    SQL;

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creator_id = $row['creator_id'];
        $title = $row['title'];
        $content = $row['content'];
        $expires = $row['expires'];
        $expiration_date = $row['expiration_date'];
        $haspassword = $row['haspassword'];
        $password = $row['password'];

        $sql = <<<'SQL'
            SELECT `username` FROM `users`
            WHERE `id` = ?;
        SQL;

        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param("i", $creator_id);
            $stmt->execute();


            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $username = $row['username'];
            } else {
                $username = "<Unknown>";
            }

            $stmt->close();
        } else {
            $con->close();
            exit("Couldn't prepare statement");
        }

        if ($expires && strtotime($expiration_date) < time()) {
            $sql = <<<'SQL'
                DELETE FROM `pastes`
                WHERE `id` = ?;
            SQL;

            if ($stmt = $con->prepare($sql)) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $con->close();
                exit("Couldn't prepare statement");
            }

            $con->close();
            exit("This paste has expired.");
        }

        if ($haspassword) {
            $con->close();
            header("Location: view-password.php?id=" . $id);
            exit();
        }

        $con->close();
    } else {
        $stmt->close();
        $con->close();
        exit("Paste not found");
    }
} else {
    $con->close();
    exit("Statement couldn't be prepared");
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
    echo "<h1 id=\"paste-title\">" . htmlspecialchars($title) . "</h1>";
    echo "<div id=\"paste-view-right\"><a href=\"/index.php\">Home</a></div>";

    if ($username === "<Unknown>") {
        echo "<p id=\"paste-author\"> By: " . htmlspecialchars($username) . "</p>";
    } else {
        echo "<p id=\"paste-author\"> By: <a href=\"/profile.php?id=" . $creator_id .
            "\">" . htmlspecialchars($username) . "</a></p>";
    }

    echo "<pre id=\"paste-content\">" . htmlspecialchars($content) . "</pre>";
    ?>
</body>

</html>