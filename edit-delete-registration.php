<?php
include_once 'sql-request.php';

// Define the total amount of records (registrations) in the table 'kurssikirjautumiset':
// We will use this info to define the amount of pages for pagination:
$total_records = count_regestrations();
// Set the maximum number of records to be shown in a single page:
$limit = 7;
// Define the amount of pages for pagination:
$total_pages = ceil($total_records / $limit);

// Read GET parameter (the number of the page) from url:
if (isset($_GET["page"])) {
    $pn  = $_GET["page"];
} else {
    $pn = 1; // define the start page - 1 (when the URL does not yet have a GET parameter)
};

// Define the start position to start our fetch:
// when the page is initially loaded, the starting index will be 0 (i.e. from the very first record in the table 'kurssikirjautumiset'):
$start_from = ($pn - 1) * $limit;

//Get an array from the DB from table 'kurssikirjautumiset' starting from index $start_from and limiting up to $limit records 
// and store in the $registration_portion variable:
$registration_portion = get_registrations($start_from, $limit);

//Get arrays with students, teachers, courses and auditories for filters:
$all_students = get_all_students();
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();

//Filtered array:
// $selected_student_registration = get_registrations(0, null, ['selected_student_id' => 583]);
// $selected_course_registration = get_registrations(0, null, ['selected_course_id' => 134]);

echo "<pre>";
// print_r($selected_student_registration);
// print_r($selected_course_registration);
// print_r($total_records);
echo "</pre>";

// If the GET parameter (?registration-id=) appears in the address in the browser (after registration's selection), then the following code is executed:
if (isset($_GET['registration-id'])) {
    // Read the URL-get-parameter named registration-id:
    $registration_id = $_GET['registration-id'];
    // Store registration's id in SESSION:
    $_SESSION["registration_id"] = $registration_id;
    // Looking for the registration with this ID in stored array with all registrations:
    foreach ($registration_portion as $registration) {
        if ($registration['tunnus'] == $registration_id) {
            // Save information (name, capacity) in the variables:
            $course_name = $registration['coursename'];
            $teacher_fullname = $registration['teachersurname'] . " " . $registration['teachername'];
            $auditory_name = $registration['auditoryname'];
            $registration_date = $registration['kirjautumispaiva'];
            $student_fullname = $registration['studentsurname'] . " " . $registration['studentname'];
            $student_grade = $registration['vuosikurssi'];
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poista/muokkaa kurssikirjautuminen</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Poista/muokkaa kurssikirjautuminen</h1>

    <!-- Section with filters -->
    <div class="filters-wrapper">
        <h2>Suodattimet</h2>
        <div class="filters">
            <label for="courses">Valitse kurssi:</label>
            <!-- Create list of all courses from the DB table "kurssit": -->
            <select id="courses" name="courses">
                <!-- The first line: -->
                <option value="empty">----valitse kurssi----</option>
                <?php
                // Run through all the entries in the array $all_courses:
                foreach ($all_courses as $course) {
                ?>
                    <!-- The value of option element is course's ID: -->
                    <!-- Put attribute 'selected' to the option with selected course - only for the updated page with GET parameter '?course-id=': -->
                    <option value="<?php echo $course["tunnus"]; ?>"

                        <?php if (isset($_SESSION["course_id"]) && $course["tunnus"] == $_SESSION["course_id"]) {
                        ?> selected <?php
                                } ?>>
                        <?php echo $course["nimi"]; ?>
                    </option>
                <?php
                }
                ?>
            </select>

            <label for="students">Valitse opiskelija:</label>
            <!-- Create list of all students from the DB table "opiskelijat": -->
            <select id="students" name="students">
                <!-- The first line: -->
                <option value="empty">----valitse opiskelija----</option>
                <?php
                // Run through all the entries in the array $all_students:
                foreach ($all_students as $student) {
                ?>
                    <!-- The value of option element is student's ID: -->
                    <!-- Put attribute 'selected' to the option with selected student - only for the updated page with GET parameter '?student-id=': -->
                    <option value="<?php echo $student["opiskelijanumero"]; ?>"

                        <?php if (isset($_SESSION["student_id"]) && $student["opiskelijanumero"] == $_SESSION["student_id"]) {
                        ?> selected <?php
                                } ?>>
                        <?php echo $student["sukunimi"] . " " . $student["etunimi"]; ?>
                    </option>
                <?php
                }
                ?>
            </select>

            <label for="teachers">Valitse opettaja:</label>
            <!-- Create list of all teachers from the DB table "opettajat": -->
            <select id="teachers" name="teachers">
                <!-- The first line: -->
                <option value="empty">----valitse opettaja----</option>
                <?php
                // Run through all the entries in the array $all_teachers:
                foreach ($all_teachers as $teacher) {
                ?>
                    <!-- The value of option element is teacher's ID: -->
                    <!-- Put attribute 'selected' to the option with selected teacher - only for the updated page with GET parameter '?teacher-id=': -->
                    <option value="<?php echo $teacher["tunnusnumero"]; ?>"

                        <?php if (isset($_SESSION["teacher_id"]) && $teacher["tunnusnumero"] == $_SESSION["teacher_id"]) {
                        ?> selected <?php
                                } ?>>
                        <?php echo $teacher["sukunimi"] . " " . $teacher["etunimi"]; ?>
                    </option>
                <?php
                }
                ?>
            </select>

            <label for="auditories">Valitse tila:</label>
            <!-- Create list of all auditories from the DB table "tilat": -->
            <select id="auditories" name="auditories">
                <!-- The first line: -->
                <option value="empty">----valitse tila----</option>
                <?php
                // Run through all the entries in the array $all_auditories:
                foreach ($all_auditories as $auditory) {
                ?>
                    <!-- The value of option element is auditory's ID: -->
                    <!-- Put attribute 'selected' to the option with selected auditory - only for the updated page with GET parameter '?auditory-id=': -->
                    <option value="<?php echo $auditory["tunnus"]; ?>"

                        <?php if (isset($_SESSION["auditory_id"]) && $auditory["tunnus"] == $_SESSION["auditory_id"]) {
                        ?> selected <?php
                                } ?>>
                        <?php echo $auditory["nimi"]; ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Section with data-table -->
    <div class="data-wrapper">
        <h2>Kurssikirjautumiset</h2>

        <table class="description-table">
            <!-- <tr>
                <th class="table-header" colspan="5">Kurssinimi - Vastaava opettaja - Kurssin tila</th>
            </tr> -->
            <tr>
                <th class="table-header-center">Kirjautumispäivä</th>
                <th class="table-header-center">Opiskelija</th>
                <th class="table-header">Kurssi</th>
                <th class="table-header-center">Poistaa</th>
                <th class="table-header-center">Muokkaa</th>
            </tr>

            <?php

            $current_course_id = null;

            // Run through all the entries in the array $registration_portion:
            foreach ($registration_portion as $registration) {
                $course_id = $registration['courseId'];
                $course_name = $registration['coursename'];
                $teacher_fullname = $registration['teachersurname'] . " " . $registration['teachername'];
                $auditory_name = $registration['auditoryname'];
                $registration_date = $registration['kirjautumispaiva'];
                $student_fullname = $registration['studentsurname'] . " " . $registration['studentname'];
                $student_grade = $registration['vuosikurssi'];

                if ($course_id !== $current_course_id) {
            ?>
                    <tr class="colspan-table-item" id="course-<?php echo $course_id; ?>">
                        <td class="table-column" colspan="3">Kurssi <b><?php echo $course_name; ?></b>, Opettaja <?php echo $teacher_fullname; ?>, tila <?php echo $auditory_name; ?></td>
                        <td class="table-column-center">
                            <input type="checkbox" id="del-course-<?php echo $course_id; ?>" name="delete" value="delete-<?php echo $course_id; ?>">
                        </td>
                        <td></td>
                    </tr>
                <?php
                    $current_course_id = $course_id;
                }
                ?>
                <tr class="table-item" id="registration-<?php echo $registration["registrationId"]; ?>">
                    <td class="table-column"><?php echo $registration_date; ?></td>
                    <td class="table-column" id="student-<?php echo $registration['studentId']; ?>"><?php echo $student_fullname; ?> (<?php echo $student_grade; ?>)</td>
                    <td class="table-column" id="course-<?php echo $registration['courseId']; ?>"><?php echo $course_name; ?></td>
                    <td class="table-column-center">
                        <input type="checkbox" id="del-registration-<?php echo $registration["registrationId"]; ?>" name="delete" value="delete-<?php echo $registration["registrationId"]; ?>">
                    </td>
                    <td class="table-column-center">
                        <input type="checkbox" id="edit-registration-<?php echo $registration["registrationId"]; ?>" name="edit" value="edit-<?php echo $registration["registrationId"]; ?>">
                    </td>

                </tr>
            <?php
            }
            ?>
        </table>
    </div>

    <!-- Section with pagination -->
    <div class="pagination-wrapper">
        <ul class="pagination-list">
            <?php
            // K is assumed to be the middle index.
            $k = (($pn + 2 > $total_pages) ? $total_pages - 2 : (($pn - 2 < 1) ? 3 : $pn));

            //initialize a variable to form a string that will contain the pagination buttons:
            $pagLink = "";

            //form the First Page (<<) and Previous Page (<) buttons, provided that the page number is greater than or equal to 2:
            if ($pn >= 2) {
                $pagLink .= "<li class='pagination-list-item pagination-list-item-marginal'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=1'><<</a></li>";
                $pagLink .= "<li class='pagination-list-item'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . ($pn - 1) . "'> Prev </a></li>";
            }

            // Show sequential links.
            for ($i = -2; $i <= 2; $i++) {
                if ($k + $i == $pn)
                // adding style by class "active-page" for active page button: 
                    $pagLink .= "<li class='pagination-list-item active-page'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . ($k + $i) . "'>" . ($k + $i) . "</a></li>";
                else
                //all another buttons, not active:
                    $pagLink .= "<li class='pagination-list-item'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . ($k + $i) . "'>" . ($k + $i) . "</a></li>";
            };

            // for ($i = 1; $i <= $total_pages; $i++) {
            //     if ($i == $pn) {
            //         // adding style by class "active-page" for active page button: 
            //         $pagLink .= "<li class='pagination-list-item active-page'><a class='pagination-list-item-link' href='edit-delete-registration.php?page="
            //             . $i . "'>" . $i . "</a></li>";
            //     } else {
            //         //all another buttons, not active:
            //         $pagLink .= "<li class='pagination-list-item'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . $i . "'>
            //                                     " . $i . "</a></li>";
            //     }
            // };

            //form the Last Page (>>) and Next Page (>) buttons, provided that the page number is greater than or equal to 2:
            if ($pn < $total_pages) {
                $pagLink .=  "<li class='pagination-list-item'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . ($pn + 1) . "'> Next </a></li>";
                $pagLink .=  "<li class='pagination-list-item pagination-list-item-marginal'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . $total_pages . "'>>></a></li>";
            }
            echo $pagLink;
            ?>
        </ul>
    </div>

</body>

</html>