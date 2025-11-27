<?php
include_once 'sql-request.php';

//Get arrays with students, teachers, courses and auditories for filters:
$all_students = get_all_students();
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();

// Store in session id from GET:
$elementName = "";
if (isset($_GET["auditory-id"]) && $_GET["auditory-id"] !== null || isset($_SESSION["auditory_id"])) {
    $_SESSION["auditory_id"] = $_GET['auditory-id'];
    foreach ($all_auditories as $a) {
        if ($a['tunnus'] == $_GET["auditory-id"]) {
            $elementName = "Tila: " . $a['nimi'];
        }
    }
} elseif (isset($_GET["student-id"]) && $_GET["student-id"] !== null || isset($_SESSION["student_id"])) {

    $_SESSION["student_id"] = $_GET['student-id'];

    // Get all records dor this student from the table aikataulu:
    $student_timetable = get_timetable_student($_SESSION["student_id"]);

    // Store name and surname of selected student:
    foreach ($all_students as $s) {
        if ($s['opiskelijanumero'] == $_GET["student-id"]) {
            $elementName = "Opiskelija: " . $s['sukunimi'] . " " . $s['etunimi'];
        }
    }
} elseif (isset($_GET["teacher-id"]) && $_GET["teacher-id"] !== null || isset($_SESSION["teacher_id"])) {
    $_SESSION["teacher_id"] = $_GET['teacher-id'];
    foreach ($all_teachers as $t) {
        if ($t['tunnusnumero'] == $_GET["teacher-id"]) {
            $elementName = "Opettaja: " . $t['sukunimi'] . " " . $t['etunimi'];
        }
    }
} elseif (isset($_GET["course-id"]) && $_GET["course-id"] !== null || isset($_SESSION["course_id"])) {
    $_SESSION["course_id"] = $_GET['course-id'];
    foreach ($all_courses as $c) {
        if ($c['tunnus'] == $_GET["course-id"]) {
            $elementName = "Kurssi: " . $c['nimi'];
        }
    }
}

//Get current year and "current" week:
date_default_timezone_set("Europe/Helsinki");
$today = new DateTime();
$current_week = date("W");
$current_year = date("o");
// Redefine the current week and year based on the date from GET parameter:
if (isset($_GET["week"])) {
    $current_dt = DateTime::createFromFormat('d.m.Y', $_GET['week']);
    $current_week = $current_dt->format('W');
    $current_year = $current_dt->format('o');
}

$current_date = new DateTime();
//set the date based on ISO-8601 calendar using year and week number:
$current_date->setISODate($current_year, $current_week);

echo "<pre>";
// print_r($student_timetable);
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarkastele aikataulua</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <h1>Tarkastele aikataulua</h1>

    <div class="filters-wrapper">
        <h2>Valitse ensin opettaja, opiskelija, kurssi tai tila:</h2>
        <div class="filters">
            <!-- Create list of all courses from the DB table "kurssit": -->
            <select id="courses" name="courses" class="filter-select">
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

            <!-- Create list of all students from the DB table "opiskelijat": -->
            <select id="students" name="students" class="filter-select">
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

            <!-- Create list of all teachers from the DB table "opettajat": -->
            <select id="teachers" name="teachers" class="filter-select">
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

            <!-- Create list of all auditories from the DB table "tilat": -->
            <select id="auditories" name="auditories" class="filter-select">
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

    <div class="week-filter-wrapper">
        <div class="filters">
            <label for="week" class="lable-style">Valitse jakso:</label>
            <a href="http://">
                <i></i>
            </a>
            <!-- Form the dropdown menu for week selection: -->
            <select name="week" id="week" class="choose-week-btn" disabled>
                <!-- Form 4 records before "current" week: -->
                <?php
                $db = clone $current_date;
                $db->modify('-4 weeks');

                for ($i = 0; $i < 4; $i++) {
                    $week_before = $db->format('W');
                    $year = $db->format('o');
                    $start_of_week = $db->format('d.m.y');

                    $end_of_week = clone $db;
                    $end_of_week->modify('+6 days');

                ?>
                    <option id="week-<?php echo $week_before; ?>-year-<?php echo $year; ?>"
                        value="<?php echo $start_of_week ?>">
                        <?php echo $week_before . "/" . $year . " (" . $start_of_week . " - " . $end_of_week->format("d.m.y") . ")"; ?>
                    </option>
                <?php
                    $db->modify('+1 week');
                }
                ?>
                <!-- "Current" week: -->
                <option selected
                    id="week-<?php echo $current_week; ?>-year-<?php echo $current_year; ?>"
                    value="
                <?php
                $current_start_of_week = $current_date->format('d.m.y');
                $current_end_of_week = (clone $current_date)->modify('+6 days')->format('d.m.y');
                echo $current_start_of_week;
                ?>">
                    <?php
                    echo $current_week . "/" . $current_year . " (" . $current_start_of_week . " - " . $current_end_of_week . ")";
                    ?>
                </option>

                <!-- Form 4 records after "current" week: -->
                <?php
                $da = clone $current_date;
                for ($i = 0; $i < 4; $i++) {
                    $da->modify('+1 week');

                    $week_after = $da->format('W');
                    $year = $da->format('o');
                    $start_of_week = $da->format("d.m.y");

                    $end_of_week = clone $da;
                    $end_of_week->modify('+6 days');

                ?>
                    <option id="week-<?php echo $week_after; ?>-year-<?php echo $year; ?>"
                        value="<?php echo $start_of_week ?>">
                        <?php echo $week_after . "/" . $year . " (" . $start_of_week . " - " . $end_of_week->format("d.m.y") . ")"; ?>
                    </option>
                <?php
                }
                ?>

            </select>
        </div>

    </div>

    <section class="timetable-wrapper" id="timetable" hidden>

        <!-- Section with information about week and the name of selected option (student's name or teacher's name or auditory or course): -->
        <div class="first-header-line">
            <span class="info-text">Vk <?php echo $current_week; ?></span>
            <span class="info-text">
                <?php echo $elementName; ?>
            </span>
        </div>

        <table class="timetable">
            <!-- The header of the table -->
            <thead>
                <tr class="tbl-header-wrap">
                    <th class="tbl-header tbl-aline-left"></th>
                    <th class="tbl-header<?php
                                            $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                                            if ($d->format('Y-m-d') == $today->format('Y-m-d')) {
                                            ?> tbl-header-today<?php
                                                            }
                                                                ?>">Ma
                        <?php
                        echo $d->format('d.m'); ?></th>

                    <th class="tbl-header<?php
                                            $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                                            $d->modify('+1 day');
                                            if ($d->format('Y-m-d') == $today->format('Y-m-d')) {
                                            ?> tbl-header-today<?php
                                                            }
                                                                ?>">Ti
                        <?php
                        echo $d->format('d.m'); ?></th>
                    <th class="tbl-header<?php
                                            $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                                            $d->modify('+2 day');
                                            if ($d->format('Y-m-d') == $today->format('Y-m-d')) {
                                            ?> tbl-header-today<?php
                                                            }
                                                                ?>">Ke
                        <?php
                        echo $d->format('d.m'); ?></th>
                    <th class="tbl-header<?php
                                            $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                                            $d->modify('+3 day');
                                            if ($d->format('Y-m-d') == $today->format('Y-m-d')) {
                                            ?> tbl-header-today<?php
                                                            }
                                                                ?>">To
                        <?php
                        echo $d->format('d.m'); ?></th>
                    <th class="tbl-header<?php
                                            $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                                            $d->modify('+4 day');
                                            if ($d->format('Y-m-d') == $today->format('Y-m-d')) {
                                            ?> tbl-header-today<?php
                                                            }
                                                                ?>">Pe
                        <?php
                        echo $d->format('d.m'); ?></th>
                </tr>
            </thead>
            <!-- Body content in a table -->
            <tbody>
                <?php

                foreach ($student_timetable[1] as $record) {
                    $start = new DateTime($record['aloitusaika']);
                    $start_time = (int) $start->format("H");
                    $end = new DateTime($record['lopetusaika']);
                    $end_time = (int) $end->format("H");
                    $diff = $start->diff($end);
                    $record['diff'] = $diff->h;
                }

                // Define the start hour for all courses:
                $startHour = 8;
                // Create 17 rows in the table:
                for ($row = 1; $row <= 17; $row++) {
                    // Calculate start time for each odd row:
                    if ($row % 2 != 0) {
                        $rowStartHour = sprintf("%02d:00", $startHour);
                    } else {
                        $rowStartHour = sprintf("%02d:30", $startHour);
                        $startHour = $startHour + 1;
                    }
                ?>
                    <tr class="tbl-row">
                        <!-- Create 6 columns in the row: -->
                        <?php
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        for ($column = 1; $column <= 6; $column++) {
                            $className = '';
                            $elementInnerText = '';
                            $elementInnerData = '';

                            if ($column == 1) {
                                $className = "tbl-content tbl-aline-left";
                                $elementInnerText = $rowStartHour;
                            } else {
                                $className = "tbl-content";
                                // Define date for each cell in the row:
                                //It will be used to fill data-day attribute to the element td:
                                $elementInnerData = $d->format("d.m.y");
                                $d = DateTime::createFromFormat('d.m.y', $elementInnerData);
                                $d->modify('+1 day');
                            }
                        ?>
                            <td class="<?php echo $className; ?>" data-day="<?php echo $elementInnerData; ?>" data-time="<?php echo $rowStartHour; ?>"><?php echo $elementInnerText; ?></td>
                        <?php

                        }
                        ?>
                    </tr>
                <?php
                }
                ?>
            </tbody>

        </table>

    </section>

    <script>
        // script to observe the option selection event for courses:
        const selectElements = document.querySelectorAll("select");
        selectElements.forEach(selectElement => {
            selectElement.addEventListener('change', formAddressPath);
        });

        // Function to form address path using id of selected course:
        function formAddressPath() {
            const itemId = this.value;
            switch (this.id) {
                case "courses":
                    window.location.href = `get-timetable-info.php?course-id=${itemId}`;
                    break;
                case "students":
                    window.location.href = `get-timetable-info.php?student-id=${itemId}`;
                    break;
                case "teachers":
                    window.location.href = `get-timetable-info.php?teacher-id=${itemId}`;
                    break;
                case "auditories":
                    window.location.href = `get-timetable-info.php?auditory-id=${itemId}`;
                    break;
                case "week":
                    if (window.location.href.includes("?course") || window.location.href.includes("?student") || window.location.href.includes("?teacher") || window.location.href.includes("?auditory")) {
                        if (window.location.href.includes("&week")) {
                            const newUrl = window.location.href.split("&week")[0];
                            window.location.href = `${newUrl}&week=${itemId}`;
                        } else {
                            window.location.href = `${window.location.href}&week=${itemId}`;
                        }
                    } else {
                        if (window.location.href.includes("?week")) {
                            const newUrl = window.location.href.split("?week")[0];
                            window.location.href = `${newUrl}?week=${itemId}`;
                        } else {
                            window.location.href = `${window.location.href}?week=${itemId}`;
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        // Function to show timetable and make week selection available:
        window.addEventListener("DOMContentLoaded", showTimetable);

        function showTimetable() {
            if (window.location.href.includes("?course") || window.location.href.includes("?student") || window.location.href.includes("?teacher") || window.location.href.includes("?auditory")) {
                const weekSelectionElement = document.getElementById("week");
                const timetableElement = document.getElementById("timetable");
                weekSelectionElement.removeAttribute("disabled");
                timetableElement.removeAttribute("hidden");
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>

</body>

</html>