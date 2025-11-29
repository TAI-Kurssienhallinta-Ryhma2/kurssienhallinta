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

function add_aikataulu($courseID, $date, $startTime, $endTime){
    global $conn;

    try {
        $stmt = $conn->prepare('INSERT INTO aikataulu (kurssi_id, paivamaara, aloitusaika, lopetusaika) VALUES (:course_id, :class_date, :aloitus, :lopetus)');
        $stmt->bindParam(':course_id', $courseID, PDO::PARAM_INT);
        $stmt->bindParam(':class_date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':aloitus', $startTime, PDO::PARAM_INT);
        $stmt->bindParam(':lopetus', $endTime, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        return false;
    }
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

function count_registrations($all_registrations)
{
    // $all_registrations = get_registrations(0);
    $total_records = count($all_registrations);
    return $total_records;
}

// Function for retrieve data from table 'kurssikirjautumiset' with different filters:
function get_registrations($start_from, $limit = null, $filters = [])
{
    global $conn;

    //Forming SQL statements:
    $sql = "SELECT kurssikirjautumiset.tunnus AS registrationId, kurssikirjautumiset.kirjautumispaiva, 
                                   opiskelijat.opiskelijanumero AS studentId, opiskelijat.etunimi AS studentname, 
                                   opiskelijat.sukunimi AS studentsurname, opiskelijat.vuosikurssi,
                                   kurssit.nimi AS coursename, kurssit.tunnus AS courseId,
                                   opettajat.sukunimi AS teachersurname, opettajat.etunimi AS teachername, 
                                   opettajat.tunnusnumero AS teacherId,
                                   tilat.nimi AS auditoryname, tilat.tunnus AS auditoryId
                            FROM  kurssikirjautumiset, opiskelijat, kurssit, opettajat, tilat
                            WHERE kurssikirjautumiset.opiskelija = opiskelijat.opiskelijanumero
                            AND   kurssikirjautumiset.kurssi = kurssit.tunnus
                            AND kurssit.opettaja = opettajat.tunnusnumero
                            AND kurssit.tila = tilat.tunnus";
                        
    //Depending on the selected filters, continue to form SQL statement:
                            if (isset($filters['selected_student_id'])) {
                                $sql .= " AND opiskelijat.opiskelijanumero = :student_id";
                            }
                            if (isset($filters['selected_teacher_id'])) {
                                $sql .= " AND opettajat.tunnusnumero = :teacher_id";
                            }
                            if (isset($filters['selected_course_id'])) {
                                $sql .= " AND kurssit.tunnus = :course_id";
                            }
                            if (isset($filters['selected_auditory_id'])) {
                                $sql .= " AND tilat.tunnus = :auditory_id";
                            }
    //Continue to form SQL statement:
                            $sql .= " ORDER BY courseId, opiskelijat.vuosikurssi, opiskelijat.sukunimi";

    //Depending on the start position and limits, continue to form SQL statement:
                            if ($limit !== null) {
                                $sql .= " LIMIT :start_from, :limit";
                            }

    $stmt = $conn->prepare($sql);

    if ($limit !== null) {
        $stmt->bindValue(':start_from', (int)$start_from, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    }
    if (isset($filters['selected_student_id'])) {
        $stmt->bindValue(':student_id', (int)$filters['selected_student_id'], PDO::PARAM_INT);
    }
    if (isset($filters['selected_teacher_id'])) {
        $stmt->bindValue(':teacher_id', (int)$filters['selected_teacher_id'], PDO::PARAM_INT);
    }
    if (isset($filters['selected_course_id'])) {
        $stmt->bindValue(':course_id', (int)$filters['selected_course_id'], PDO::PARAM_INT);
    }
    if (isset($filters['selected_auditory_id'])) {
        $stmt->bindValue(':auditory_id', (int)$filters['selected_auditory_id'], PDO::PARAM_INT);
    }

    $stmt->execute();

    $all_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $all_registrations;
}

function get_course_by_id($course_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT kurssit.tunnus, kurssit.nimi, kurssit.kuvaus, 
                            kurssit.alkupaiva, kurssit.loppupaiva, kurssit.tila, 
                            kurssit.opettaja,
                            opettajat.etunimi, opettajat.sukunimi, opettajat.aine
                            FROM kurssit
                            JOIN opettajat ON kurssit.opettaja = opettajat.tunnusnumero
                            WHERE kurssit.tunnus = :course_id");
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();

    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    return $course;
}

/**
 * Fetches the timetable for a specific teacher.
 *
 * This function returns an array with two elements:
 * 1. Teacher's full name (concatenation of first and last name).
 * 2. An array of timetable entries for the teacher, including course name,
 *    room name, date, start time, and end time.
 *
 * The optional $date parameter filters the timetable by a specific date.
 * The date must be provided as a string in the format 'dd.mm.yyyy' (e.g., '02.02.2024').
 *
 * @param int         $teacher_id The unique ID of the teacher.
 * @param string|null $date       Optional. The date to filter timetable entries, format 'dd.mm.yyyy'.
 *
 * @return array An array containing:
 *               - [0]: associative array with key 'opettajan_nimi' (teacher's full name)
 *               - [1]: array of timetable entries for the teacher, possibly filtered by date
 */
function get_timetable_teacher($teacher_id, $date = null) {
    global $conn;
    $data = [];

    //---------------------First query----------------------

    $query = "SELECT CONCAT(opettajat.etunimi, ' ', opettajat.sukunimi) AS opettajan_nimi
            FROM opettajat 
            WHERE opettajat.tunnusnumero = :teacher_id";

    $statement = $conn->prepare($query);

    $statement->execute([
        ":teacher_id" => $teacher_id
    ]);

    //Append it into the $data array
    $data[] = $statement->fetch(PDO::FETCH_ASSOC);

    //----------------------Second Query-----------------------

    $query = "SELECT 
                kurssit.nimi as kurssin_nimi,
                tilat.nimi as tilan_nimi,
                DATE_FORMAT(aikataulu.paivamaara, '%d.%m.%Y') as paivamaara,
                aikataulu.aloitusaika,
                aikataulu.lopetusaika
            FROM kurssit
            INNER JOIN opettajat ON kurssit.opettaja = opettajat.tunnusnumero
            INNER JOIN aikataulu ON kurssit.tunnus = aikataulu.kurssi_id
            INNER JOIN tilat ON kurssit.tila = tilat.tunnus
            WHERE opettajat.tunnusnumero = :teacher_id";

    if($date !== null) {
        $query .= " AND aikataulu.paivamaara = STR_TO_DATE(:date, '%d.%m.%Y')";
    }

    $statement = $conn->prepare($query);

    if($date !== null) {
        $statement->execute([
            ":teacher_id" => $teacher_id,
            ":date" => $date
        ]);
    } else {
        $statement->execute([
            ":teacher_id" => $teacher_id
        ]);
    }

    //Append it into the $data array
    $data[] = $statement->fetchAll(PDO::FETCH_ASSOC);

    //----------------------------------------------------------------------

    return $data;
}

/**
 * Fetches the timetable for a specific student.
 *
 * This function returns an array with two elements:
 * 1. Student's full name (concatenation of first and last name).
 * 2. An array of timetable entries for the student, including course name,
 *    date, start time, end time, teacher's full name, and room name.
 *
 * The optional $date parameter filters the timetable by a specific date.
 * The date must be provided as a string in the format 'dd.mm.yyyy' (e.g., '02.02.2024').
 *
 * @param int         $student_id The unique ID of the student.
 * @param string|null $date       Optional. The date to filter timetable entries, format 'dd.mm.yyyy'.
 *
 * @return array An array containing:
 *               - [0]: associative array with key 'opiskelijan_nimi' (student's full name)
 *               - [1]: array of timetable entries for the student, possibly filtered by date
 */
function get_timetable_student($student_id, $date = null) {
    global $conn;
    $data = [];

    //---------------------First query----------------------

    $query = "SELECT CONCAT(opiskelijat.etunimi, ' ', opiskelijat.sukunimi) AS opiskelijan_nimi 
            FROM opiskelijat 
            WHERE opiskelijat.opiskelijanumero = :student_id";

    $statement = $conn->prepare($query);

    $statement->execute([
        ":student_id" => $student_id
    ]);

    //Append it into the $data array
    $data[] = $statement->fetch(PDO::FETCH_ASSOC);

    //----------------------Second Query-----------------------

    $query = "SELECT
                kurssit.nimi as kurssin_nimi,
                DATE_FORMAT(aikataulu.paivamaara, '%d.%m.%Y') as paivamaara,
                aikataulu.aloitusaika,
                aikataulu.lopetusaika,
                CONCAT(opettajat.etunimi, ' ', opettajat.sukunimi) as opettajan_nimi,
                tilat.nimi as tilan_nimi
            FROM kurssikirjautumiset
            INNER JOIN opiskelijat ON kurssikirjautumiset.opiskelija = opiskelijat.opiskelijanumero
            INNER JOIN kurssit ON kurssikirjautumiset.kurssi = kurssit.tunnus
            INNER JOIN aikataulu ON kurssit.tunnus = aikataulu.kurssi_id
            INNER JOIN opettajat ON kurssit.opettaja = opettajat.tunnusnumero
            INNER JOIN tilat ON kurssit.tila = tilat.tunnus
            WHERE opiskelijat.opiskelijanumero = :student_id";

    if($date !== null) {
        $query .= " AND aikataulu.paivamaara = STR_TO_DATE(:date, '%d.%m.%Y')";
    }

    $statement = $conn->prepare($query);

    if($date !== null) {
        $statement->execute([
            ":student_id" => $student_id,
            ":date" => $date
        ]);
    } else {
        $statement->execute([
            ":student_id" => $student_id
        ]);
    }

    //Append it into the $data array
    $data[] = $statement->fetchAll(PDO::FETCH_ASSOC);

    //----------------------------------------------------------------------

    return $data;
}

/**
 * Fetches timetable information for a specific room (auditory).
 *
 * Returns an array with two elements:
 * 1. Room information with its name.
 * 2. An array of distinct courses scheduled in that room, optionally filtered by date.
 *
 * The optional $date parameter filters the courses by a specific date.
 * The date must be provided as a string in the format 'dd.mm.yyyy' (e.g., '02.02.2024').
 *
 * @param int         $auditory_id The unique ID of the room.
 * @param string|null $date        Optional. The date to filter courses, format 'dd.mm.yyyy'.
 *
 * @return array An array containing:
 *               - [0]: associative array with key 'tilan_nimi' (room name)
 *               - [1]: array of distinct courses in the room, possibly filtered by date
 */
function get_timetable_auditory($auditory_id, $date = null) {
    global $conn;
    $data = [];

    //---------------------First query----------------------

    $query = "SELECT tilat.nimi as tilan_nimi
            FROM tilat 
            WHERE tilat.tunnus = :auditory_id";

    $statement = $conn->prepare($query);

    $statement->execute([
        ":auditory_id" => $auditory_id
    ]);

    //Append it into the $data array
    $data[] = $statement->fetch(PDO::FETCH_ASSOC);

    //----------------------Second Query-----------------------

    $query = "SELECT DISTINCT 
                kurssit.nimi as kurssin_nimi,
                kurssit.kuvaus,
                DATE_FORMAT(kurssit.alkupaiva, '%d.%m.%Y') as alkupaiva,
                DATE_FORMAT(kurssit.loppupaiva, '%d.%m.%Y') as loppupaiva
            FROM kurssit
            INNER JOIN tilat ON kurssit.tila = tilat.tunnus
            INNER JOIN aikataulu ON kurssit.tunnus = aikataulu.kurssi_id
            WHERE tilat.tunnus = :auditory_id";

    if($date !== null) {
        $query .= " AND aikataulu.paivamaara = STR_TO_DATE(:date, '%d.%m.%Y')";
    }

    $statement = $conn->prepare($query);

    if($date !== null) {
        $statement->execute([
            ":auditory_id" => $auditory_id,
            ":date" => $date
        ]);
    } else {
        $statement->execute([
            ":auditory_id" => $auditory_id
        ]);
    }

    //Append it into the $data array
    $data[] = $statement->fetchAll(PDO::FETCH_ASSOC);

    //----------------------------------------------------------------------

    return $data;
}
