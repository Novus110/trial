<?php
// Quiz database connection
$quiz_db_host = "localhost";
$quiz_db_user = "root";
$quiz_db_pass = "";
$quiz_db_name = "try_quiz";

$quiz_conn = new mysqli($quiz_db_host, $quiz_db_user, $quiz_db_pass, $quiz_db_name);

// Error handling for quiz database connection
if ($quiz_conn->connect_error) {
    die("Quiz database connection failed: " . $quiz_conn->connect_error);
}


// Character creator database connection
$char_db_host = "localhost";
$char_db_user = "root";
$char_db_pass = "";
$char_db_name = "character_creator";

$char_conn = new mysqli($char_db_host, $char_db_user, $char_db_pass, $char_db_name);

// Error handling for character creator database connection
if ($char_conn->connect_error) {
    die("Character creator database connection failed: " . $char_conn->connect_error);
}

// Example query with error handling for quiz database
$sql_quiz = "SELECT * FROM quiz_scores";
$result_quiz = $quiz_conn->query($sql_quiz);
if ($result_quiz) {
    while ($row = $result_quiz->fetch_assoc()) {
        print_r($row);
    }
    $result_quiz->free_result(); // Free result set memory
} else {
    echo "Error fetching data from quiz_scores: " . $quiz_conn->error;
}


// Example query with error handling for character creator database
$sql_char = "SELECT * FROM users";
$result_char = $char_conn->query($sql_char);
if ($result_char) {
    while ($row = $result_char->fetch_assoc()) {
        print_r($row);
    }
    $result_char->free_result(); // Free result set memory
} else {
    echo "Error fetching data from users: " . $char_conn->error;
}

$quiz_conn->close();
$char_conn->close();

?>