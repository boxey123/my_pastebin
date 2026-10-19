<?php

session_start();
header("Content-Type: application/json");

function response($title, $content, $author = null, $authorID = null)
{
    return json_encode(array(
        "title" => $title,
        "content" => $content,
        "author" => $author,
        "author_id" => $authorID
    ));
}

function passwordInvalid($content)
{
    return response("Failed", $content);
}

function passwordSuccess($title, $content, $author, $authorID)
{
    return response($title, $content, $author, $authorID);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit(passwordInvalid("Invalid request method"));
}

if (!isset($_POST["id"], $_POST["password"])) {
    exit(passwordInvalid("Missing data"));
}

if (empty($_POST["id"]) || empty($_POST["password"])) {
    exit(passwordInvalid("Missing data"));
}

$id = (int) $_POST["id"];
$password = $_POST["password"];

if ($id <= 0) {
    exit(passwordInvalid("Invalid id provided"));
}

if (!is_string($password)) {
    exit(passwordInvalid("Invalid password provided"));
}

$con = require("db_connection.php");

$sql = <<<'SQL'
    SELECT `title`, `content`, `creator_id`, `password`, `expires`, `expiration_date`
    FROM `pastes`
    WHERE `id` = ?
    SQL;

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $content = $row['content'];
        $creatorID = $row['creator_id'];
        $hash = $row['password'];
        $expires = (bool) $row['expires'];
        $expiration_date = $row['expiration_date'];

        if ($expires) {
            if (!is_string($expiration_date)) {
                $con->close();
                exit(passwordInvalid("Invalid expiration date"));
            }

            $expirationTimestamp = strtotime($expiration_date);
            if ($expirationTimestamp === false) {
                $con->close();
                exit(passwordInvalid("Invalid expiration date"));
            }
        }

        if ($expires && $expirationTimestamp < time()) {
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
                exit(passwordInvalid("Couldn't prepare statement"));
            }

            $con->close();
            exit(passwordInvalid("This paste has expired."));
        }

        if (!is_string($hash)) {
            $con->close();
            exit(passwordInvalid("Invalid password hash"));
        }

        if (password_verify($password, $hash)) {
            // get author name

            $sql = <<<'SQL'
                SELECT `username` FROM `users`
                WHERE `id` = ?;
            SQL;

            if ($stmt = $con->prepare($sql)) {
                $stmt->bind_param("i", $creatorID);
                $stmt->execute();

                $result2 = $stmt->get_result();

                if ($result2->num_rows > 0) {
                    $row2 = $result2->fetch_assoc();
                    $author = $row2['username'];

                    echo passwordSuccess($title, $content, $author, $creatorID);
                } else {
                    echo passwordInvalid("Author not found");
                }
            } else {
                echo passwordInvalid("Couldn't prepare statement");
            }
        } else {
            echo passwordInvalid("Incorrect password");
        }
    } else {
        echo passwordInvalid("Paste not found");
    }
} else {
    echo passwordInvalid("Couldn't prepare statement");
}
