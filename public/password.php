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

function invalid($content)
{
    return response("Failed", $content);
}

function success($title, $content, $author, $authorID)
{
    return response($title, $content, $author, $authorID);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit(invalid("Invalid request method"));
}

if (!isset($_POST["id"], $_POST["password"])) {
    exit(invalid("Missing data"));
}

if (empty($_POST["id"]) || empty($_POST["password"])) {
    exit(invalid("Missing data"));
}

$id = (int) $_POST["id"];
$password = $_POST["password"];

if ($id <= 0) {
    exit(invalid("Invalid id provided"));
}

if (!is_string($password)) {
    exit(invalid("Invalid password provided"));
}

include("db_connection.php");

$sql = <<<'SQL'
    SELECT `title`, `content`, `creator_id`, `password`, `expires`, `expiration_date`
    FROM `pastes`
    WHERE `id` = ?
    SQL;

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($title, $content, $creatorID, $hash, $expires, $expiration_date);
        $stmt->fetch();

        if ($expires) {
            if (!is_string($expiration_date)) {
                $con->close();
                exit(invalid("Invalid expiration date"));
            }

            $expirationTimestamp = strtotime($expiration_date);
            if ($expirationTimestamp === false) {
                $con->close();
                exit(invalid("Invalid expiration date"));
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
                exit(invalid("Couldn't prepare statement"));
            }

            $con->close();
            exit(invalid("This paste has expired."));
        }

        if (!is_string($hash)) {
            $con->close();
            exit(invalid("Invalid password hash"));
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
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($author);
                    $stmt->fetch();

                    echo success($title, $content, $author, $creatorID);
                } else {
                    echo invalid("Author not found");
                }
            } else {
                echo invalid("Couldn't prepare statement");
            }
        } else {
            echo invalid("Incorrect password");
        }
    } else {
        echo invalid("Paste not found");
    }
} else {
    echo invalid("Couldn't prepare statement");
}
