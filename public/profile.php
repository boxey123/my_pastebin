<?php
session_start();

$con = require("db_connection.php");

$found = false;
$viewType = "";

if (isset($_GET["id"])) {
    $sql = <<<'SQL'
        SELECT `username` FROM `users`
        WHERE `id` = ?;
    SQL;

    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $_GET["id"]);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
        } else {
            $stmt->close();
            $con->close();
            exit("User not found.");
        }
    } else {
        $con->close();
        exit("Couldn't prepare statement");
    }

    $stmt->close();
} else {
    if (!isset($_SESSION["isLoggedIn"])) {
        exit("You aren't logged in. Log in <a href=\"/login.php\">here</a>");
    }

    $username = $_SESSION["username"];
    $found = true;
    $viewType = "Private Profile";
}

if (!$found) {
    exit("User not found.");
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo htmlspecialchars($username) . " | " . $viewType; ?></title>
</head>

<body>
    <div>
        <h1 id="profile-username">User: <?php echo htmlspecialchars($username); ?></h1>
        <?php
        if ($viewType == "Private Profile") {
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
            $con = require("db_connection.php");

            if ($viewType == "Public Profile") {
                $sql = <<<'SQL'
                    SELECT `id`, `title` FROM `pastes`
                    WHERE `creator_id` = ? AND `visibility` = 'public';
                    SQL;

                if ($stmt = $con->prepare($sql)) {
                    $stmt->bind_param("s", $_GET["id"]);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $id = $row['id'];
                    $title = $row['title'];

                    while ($stmt->fetch()) {
                        echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars($title) . "</a><br>";
                    }
                } else {
                    $stmt->close();
                    $con->close();
                    exit("Couldn't prepare statement");
                }
            } else {
                $sql = <<<'SQL'
                    SELECT `id`, `title` FROM `pastes`
                    WHERE `creator_id` = ?;
                SQL;

                if ($stmt = $con->prepare($sql)) {
                    $stmt->bind_param("i", $_SESSION["userID"]);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $id = $row['id'];
                    $title = $row['title'];

                    while ($stmt->fetch()) {
                        echo "<a href=\"/view.php?id=" . $id . "\">" . htmlspecialchars($title) . "</a><br>";
                    }
                } else {
                    $stmt->close();
                    $con->close();
                    exit("Couldn't prepare statement");
                }
            }
            ?>
        </div>
    </div>
</body>

</html>