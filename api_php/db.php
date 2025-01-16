<?php

function getConnection() {
    $host = "localhost";
    $db_name = "rpg_game"; // Ganti nama database menjadi rpg_game
    $username = "root";
    $password = "";

    $conn = new mysqli($host, $username, $password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
