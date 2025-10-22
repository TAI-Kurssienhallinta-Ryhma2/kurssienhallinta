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

function get_student_registrations($student_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT kurssit.alkupaiva, kurssit.nimi 
                            FROM  kurssikirjautumiset, kurssit
                            WHERE  kurssikirjautumiset.opiskelija =  $student_id
                            AND kurssikirjautumiset.kurssi = kurssit.tunnus
                            ORDER BY kurssit.alkupaiva DESC;");
    $stmt->execute(); // Run the request

    // Take all selected student's registration that we just fetched and put it into an array:
    $student_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $student_registrations;
}

function get_all_teachers()
{
    global $conn;

    //Send request to the DB to the table opettajat without any parameters
    // because we need to get all the teachers from the table:
    $stmt = $conn->prepare("SELECT tunnusnumero, etunimi, sukunimi, aine 
                            FROM  opettajat
                            ORDER BY sukunimi;");
    $stmt->execute(); // Run the request

    // Take all the teachers that we just fetched and put it into an array:
    $all_teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $all_teachers;
}

function get_teachers_course($teacher_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT kurssit.nimi, kurssit.alkupaiva, kurssit.loppupaiva, kurssit.tila
                            FROM  opettajat, kurssit
                            WHERE  kurssit.opettaja = $teacher_id
                            AND kurssit.opettaja = opettajat.tunnusnumero
                            ORDER BY kurssit.nimi;");
    $stmt->execute(); // Run the request

    // Take all selected teacher's courses that we just fetched and put it into an array:
    $teacher_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $teacher_courses;
}