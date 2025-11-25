<?php
include_once 'sql-request.php';

//Get arrays with students, teachers, courses and auditories for filters:
$all_students = get_all_students();
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();

// Store in session id from GET:
if (isset($_GET["auditory-id"]) && $_GET["auditory-id"] !== null) {
    $_SESSION["auditory_id"] = $_GET['auditory-id'];
} elseif (isset($_GET["student-id"]) && $_GET["student-id"] !== null) {
    $_SESSION["student_id"] = $_GET['student-id'];
} elseif (isset($_GET["teacher-id"]) && $_GET["teacher-id"] !== null) {
    $_SESSION["teacher_id"] = $_GET['teacher-id'];
} elseif (isset($_GET["course-id"]) && $_GET["course-id"] !== null) {
    $_SESSION["course_id"] = $_GET['course-id'];
}

//Get today data:
date_default_timezone_set("Europe/Helsinki");
$today_date = date("Y.m.d");
$current_week = date("W");
$current_year = date("Y");
$d = new DateTime();
$d->setISODate($current_year, $current_week);
$current_start_of_week = $d->format("d.m.y");
$d->modify('+6 days');
$current_end_of_week = $d->format("d.m.y");

echo "<pre>";
//  print_r($current_week);
//  print_r($current_year);
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
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Tarkastele aikataulua</h1>

    <div class="filters-wrapper">
        <h2>Suodattimet</h2>
        <div class="filters">
            <!-- <label for="courses">Valitse kurssi:</label> -->
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

            <!-- <label for="students">Valitse opiskelija:</label> -->
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

            <!-- <label for="teachers">Valitse opettaja:</label> -->
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

            <!-- <label for="auditories">Valitse tila:</label> -->
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
            <label for="choose-week-btn">Valitse jakso:</label>
            <!-- Form the dropdown menu for week selection: -->
            <select name="choose-week-btn" id="choose-week-btn" class="choose-week-btn">
                <!-- Form 4 records before "current" week: -->
                <?php
                $week_before = $current_week - 5;
                for ($i = 0; $i < 4; $i++) {
                    $week_before = $week_before + 1;
                    $db = new DateTime();
                    $db->setISODate($current_year, $week_before);
                    $start_of_week = $db->format("d.m.y");
                    $db->modify('+6 days');
                    $end_of_week = $db->format("d.m.y");
                ?>
                    <option id="week-<?php echo $week_before; ?>-year-<?php echo $current_year; ?>" value="<?php echo $week_before . "/" . $current_year; ?>"><?php echo $week_before . "/" . $current_year . " (" . $start_of_week . " - " . $end_of_week . ")"; ?></option>
                <?php
                }
                ?>
                <!-- "Current" week: -->
                <option selected id="week-<?php echo $current_week; ?>-year-<?php echo $current_year; ?>" value="<?php echo $current_week . "/" . $current_year; ?>"><?php echo $current_week . "/" . $current_year. " (" . $current_start_of_week . " - " . $current_end_of_week . ")"; ?></option>

                <!-- Form 4 records after "current" week: -->
                <?php
                $week_after = $current_week;
                for ($i = 0; $i < 4; $i++) {
                    $week_after = $week_after + 1;
                    $da = new DateTime();
                    $da->setISODate($current_year, $week_after);
                    $start_of_week = $da->format("d.m.y");
                    $da->modify('+6 days');
                    $end_of_week = $da->format("d.m.y");
                ?>
                    <option id="week-<?php echo $week_after; ?>-year-<?php echo $current_year; ?>" value="<?php echo $week_after . "/" . $current_year; ?>"><?php echo $week_after . "/" . $current_year . " (" . $start_of_week . " - " . $end_of_week . ")"; ?></option>
                <?php
                }
                ?>

            </select>
        </div>

    </div>



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
                default:
                    break;
            }
        }
    </script>

</body>

</html>