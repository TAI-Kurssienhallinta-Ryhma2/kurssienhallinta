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

function get_all_courses()
{
    global $conn;

    //Send request to the DB to the table kurssit without any parameters
    // because we need to get all the courses from the table:
    $stmt = $conn->prepare("SELECT tunnus, nimi, kuvaus, alkupaiva, loppupaiva, tila, opettajat.etunimi, opettajat.sukunimi 
                            FROM  kurssit, opettajat
                            WHERE kurssit.opettaja = opettajat.tunnusnumero
                            ORDER BY nimi;");
    $stmt->execute();

    $all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $all_courses;
}

function get_students_registered_for_course($course_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT opiskelijat.etunimi, opiskelijat.sukunimi, opiskelijat.vuosikurssi
                            FROM  opiskelijat, kurssikirjautumiset
                            WHERE  kurssikirjautumiset.kurssi = $course_id
                            AND opiskelijat.opiskelijanumero = kurssikirjautumiset.opiskelija
                            ORDER BY opiskelijat.sukunimi;");
    $stmt->execute();

    $registered_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $registered_students;
}

function add_auditory($name, $capacity)
{
    global $conn;

    try {
        // Lisätään uusi tila tietokantaan
        $stmt = $conn->prepare("INSERT INTO tilat (nimi, kapasiteetti) VALUES (:nimi, :kapasiteetti)");
        $stmt->bindParam(':nimi', $name, PDO::PARAM_STR);
        $stmt->bindParam(':kapasiteetti', $capacity, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        return false;
    }
}
function auditory_name_exists($name)
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM tilat WHERE nimi = :nimi");
        $stmt->bindParam(':nimi', $name, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function course_name_exists($name)
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM kurssit WHERE nimi = :nimi");
        $stmt->bindParam(':nimi', $name, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function add_course($name, $description, $start_date, $end_date, $auditory_id, $teacher_id)
{
    global $conn;

    try {
        $stmt = $conn->prepare("INSERT INTO kurssit (nimi, kuvaus, alkupaiva, loppupaiva, tila, opettaja) 
                                VALUES (:nimi, :kuvaus, :alkupaiva, :loppupaiva, :tila, :opettaja)");
        $stmt->bindParam(':nimi', $name, PDO::PARAM_STR);
        $stmt->bindParam(':kuvaus', $description, PDO::PARAM_STR);
        $stmt->bindParam(':alkupaiva', $start_date, PDO::PARAM_STR);
        $stmt->bindParam(':loppupaiva', $end_date, PDO::PARAM_STR);
        $stmt->bindParam(':tila', $auditory_id, PDO::PARAM_INT);
        $stmt->bindParam(':opettaja', $teacher_id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function get_all_auditories()
{
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT tunnus, nimi, kapasiteetti 
                                FROM tilat
                                ORDER BY nimi;");
        $stmt->execute();

        $all_auditories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $all_auditories;
    } catch (PDOException $e) {
        return [];
    }
}

function get_courses_by_auditory_id($auditory_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT nimi, opettajat.etunimi, opettajat.sukunimi, alkupaiva, loppupaiva, tila, tunnus 
                            FROM  kurssit, opettajat
                            WHERE  kurssit.tila = $auditory_id
                            AND opettajat.tunnusnumero = kurssit.opettaja
                            ORDER BY kurssit.nimi;");
    $stmt->execute();

    $reserved_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $reserved_courses;
}

function calculate_registered_students_for_course($reserved_courses)
{
    $all_registered_students = [];
    if (!empty($reserved_courses)) {
        foreach ($reserved_courses as $course) {
            $course_id = $course['tunnus'];
            $registered_students_for_course = get_students_registered_for_course($course_id);
            if (!empty($registered_students_for_course)) {
                $all_registered_students[$course_id] = count($registered_students_for_course);
            } else {
                $all_registered_students[$course_id] = 0;
            }
        }
    }
    return $all_registered_students;
}

function add_teacher($firstname, $lastname, $subject)
{
    global $conn;

    try {

        $stmt = $conn->prepare("INSERT INTO opettajat (etunimi, sukunimi, aine) VALUES (:etunimi, :sukunimi, :aine)");
        $stmt->bindParam(':etunimi', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':sukunimi', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':aine', $subject, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        return false;
    }
}
