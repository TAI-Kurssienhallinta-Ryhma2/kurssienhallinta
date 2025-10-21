<?php
include_once 'connection.php';

function get_all_students()
{
    global $conn;

    //Send request to the DB to the table opiskelijat without any parameters
    // because we need to get all the students from the table:
    $stmt = $conn->prepare("SELECT opiskelijanumero, etunimi, sukunimi, syntymapaiva, vuosikurssi 
                            FROM  opiskelijat
                            ORDER BY sukunimi;");
    $stmt->execute(); // Run the request

    // Take all the students that we just fetched and put it into an array:
    $all_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $all_students;
}

?>