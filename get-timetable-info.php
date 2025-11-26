<?php
include_once 'sql-request.php';

//Get arrays with students, teachers, courses and auditories for filters:
$all_students = get_all_students();
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();

// Store in session id from GET:
if (isset($_GET["auditory-id"]) && $_GET["auditory-id"] !== null || isset($_SESSION["auditory_id"])) {
    $_SESSION["auditory_id"] = $_GET['auditory-id'];
} elseif (isset($_GET["student-id"]) && $_GET["student-id"] !== null || isset($_SESSION["student_id"])) {
    $_SESSION["student_id"] = $_GET['student-id'];
} elseif (isset($_GET["teacher-id"]) && $_GET["teacher-id"] !== null || isset($_SESSION["teacher_id"])) {
    $_SESSION["teacher_id"] = $_GET['teacher-id'];
} elseif (isset($_GET["course-id"]) && $_GET["course-id"] !== null || isset($_SESSION["course_id"])) {
    $_SESSION["course_id"] = $_GET['course-id'];
}

//Get current year and "current" week:
date_default_timezone_set("Europe/Helsinki");
$current_week = date("W");
$current_year = date("Y");
if (isset($_GET["week"])) {
    $current_week = DateTime::createFromFormat('d.m.Y', $_GET['week'])->format('W');
}

$d = new DateTime();
//set the date based on ISO-8601 calendar using year and week number:
$d->setISODate($current_year, $current_week);
//Define the date of the Monday of the current week:
$current_start_of_week = $d->format("d.m.y");
//Add 6 days to the Monday of the current week:
$d->modify('+6 days');
//Define the date of the Sunday of the current week:
$current_end_of_week = $d->format("d.m.y");

echo "<pre>";
//  print_r($_SESSION["auditory_id"]);
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
        <h2>Suodattimet</h2>
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
            <label for="week">Valitse jakso:</label>
            <a href="http://">
                <i></i>
            </a>
            <!-- Form the dropdown menu for week selection: -->
            <select name="week" id="week" class="choose-week-btn">
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
                    <option id="week-<?php echo $week_before; ?>-year-<?php echo $current_year; ?>" value="<?php echo $start_of_week ?>"><?php echo $week_before . "/" . $current_year . " (" . $start_of_week . " - " . $end_of_week . ")"; ?></option>
                <?php
                }
                ?>
                <!-- "Current" week: -->
                <option selected id="week-<?php echo $current_week; ?>-year-<?php echo $current_year; ?>" value="<?php echo $current_start_of_week ?>"><?php echo $current_week . "/" . $current_year . " (" . $current_start_of_week . " - " . $current_end_of_week . ")"; ?></option>

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
                    <option id="week-<?php echo $week_after; ?>-year-<?php echo $current_year; ?>" value="<?php echo $start_of_week ?>"><?php echo $week_after . "/" . $current_year . " (" . $start_of_week . " - " . $end_of_week . ")"; ?></option>
                <?php
                }
                ?>

            </select>
        </div>

    </div>

    <section class="timetable-wrapper">
        <table class="timetable">
            <!-- The header of the table -->
            <thead>
                <tr>
                    <th class="tbl-header tbl-timedata"></th>
                    <th class="tbl-header">Ma 
                        <?php 
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        echo $d->format('d.m');?></th>
                    <th class="tbl-header">Ti
                        <?php
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        $d->modify('+1 day');
                        echo $d->format('d.m');
                        ?>
                    </th>
                    <th class="tbl-header">Ke 
                    <?php
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        $d->modify('+2 day');
                        echo $d->format('d.m');
                        ?>
                    </th>
                    <th class="tbl-header">To 
                    <?php
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        $d->modify('+3 day');
                        echo $d->format('d.m');
                        ?>
                    </th>
                    <th class="tbl-header">Pe 
                    <?php
                        $d = DateTime::createFromFormat('d.m.y', $current_start_of_week);
                        $d->modify('+4 day');
                        echo $d->format('d.m');
                        ?>
                    </th>
                </tr>
            </thead>
            <!-- Body content in a table -->
            <tbody>
                <tr class="tbl-row">
                    <td class="tbl-content tbl-timedata">08:00</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">09:00</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">10:00</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">11:00</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">12:30</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">13:30</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">14:30</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>
                <tr class="tbl-row">
                    <td class="tbl-timedata">15:30</td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                    <td class="tbl-content"></td>
                </tr>

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
    </script>
    <?php include 'footer.php'; ?>

</body>

</html>