<?php
    session_start();

    if ( !isset( $_GET["id"] ) ) {
        exit( "No id provided" );
    }

    if ( empty( $_GET["id"] ) ) {
        exit( "No id provided" );
    }

    $id = (int) $_GET["id"];

    if ( $id <= 0 ) {
        exit( "Invalid id provided" );
    }

    include( "db_connection.php" );

    if ( $stmt = $con->prepare( "SELECT `haspassword`, `expires`, `expiration_date` FROM `pastes` WHERE `id` = ?" ) ) {
        $stmt->bind_param( "i", $id );
        $stmt->execute();
        $stmt->store_result();

        if ( $stmt->num_rows > 0 ) {
            $stmt->bind_result( $haspassword, $expires, $expiration_date );
            $stmt->fetch();

            if ( !$haspassword ) {
                $stmt->close();
                $con->close();
                header( "Location: view.php?id=" . $id );
                exit();
            }

            if ( $expires && strtotime( $expiration_date ) < time() ) {
                if ( $stmt = $con->prepare( "DELETE FROM `pastes` WHERE `id` = ?" ) ) {
                    $stmt->bind_param( "i", $id );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $con->close();
                    exit( "Couldn't prepare statement" );
                }

                $con->close();
                exit( "This paste has expired." );
            }

        } else {
            $stmt->close();
            $con->close();
            exit( "Paste not found" );
         }

    } else {
        $con->close();
        exit( "Couldn't prepare statement" );
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title id="pageTitle">Paste View</title>
</head>
<body>
    <div id="paste-view-right"><a href="/index.php">Home</a></div>
    <div id="passwordDiv">
        <script>
            async function send() {
                const params = new URLSearchParams(document.location.search);
                const password = document.getElementById("password").value;
                const id = params.get("id");
                const passwordResult = document.getElementById("passwordResult"); 

                if ( password.length > 0 ) {
                    const formData = new FormData();
                    formData.append("password", password);
                    formData.append("id", id);

                    const response = await fetch("/password.php", {
                        method: "POST",
                        body: formData
                    });
                    const result = await response.json();

                    const contentDiv = document.getElementById("contentDiv");
                    const passwordDiv = document.getElementById("passwordDiv");

                    const title = document.getElementById("paste-title");
                    const author = document.getElementById("paste-author");
                    const content = document.getElementById("paste-content");

                    if ( result.title != "Failed" ) {
                        passwordDiv.style.display = "none";
                        contentDiv.style.display = "block";

                        title.textContent = result.title;
                        author.textContent = "By: ";
                        const authorLink = document.createElement("a");
                        authorLink.href = "/profile.php?id=" + encodeURIComponent(result.author_id);
                        authorLink.textContent = result.author;
                        author.appendChild(authorLink);
                        content.textContent = result.content;
                    } else { 
                        passwordResult.style.color = "red";
                        passwordResult.textContent = "Invalid Password Submitted";
                    }

                } else {
                    passwordResult.style.color = "red";
                    passwordResult.textContent = "Invalid Password Submitted";
                }
            }
        </script>
        <label for="password">Password</label> 
        <input type="password" name="password" id="password">
        <button onclick="send()">Send</button>
        <p id="passwordResult"></p>
    </div>
    <div style="display: none;" id="contentDiv">
        <h1 id="paste-title"></h1><p id="paste-author"></p>
        <pre id="paste-content"> </pre>
    </div>
</body>
</html>