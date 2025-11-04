<?php
include_once 'sql-request.php';

// Get an array with alle courses from the database table 'kurssit':
$all_courses = get_all_courses();
// echo "<pre>";
// print_r($all_courses);
// echo "</pre>";

// If the GET parameter (?course-id=) appears in the address in the browser (after course's selection), then the following code is executed:
if (isset($_GET['course-id'])) {
    // Read the URL-get-parameter named course-id:
    $course_id = $_GET['course-id'];
    // Store course's id in SESSION:
    $_SESSION["course_id"] = $course_id;
    // Looking for the course with this ID in stored array with all courses:
    foreach ($all_courses as $course) {
        if ($course['tunnus'] == $course_id) {
            // Save information (name, description, the start date and end date, teacher's name and auditory in the variables:
            $course_name = $course['nimi'];
            $course_description = $course['kuvaus'];
            $course_start_date = $course['alkupaiva'];
            $course_end_date = $course['loppupaiva'];
            $course_auditory = $course['tila'];
            $course_teacher = $course['sukunimi'] . " " . $course['etunimi'];
            break;
        }
    }

    // Get an array with all registrations from the DB table "kurssikirjautumiset" for the SELECTED course:
    $registered_students = get_students_registered_for_course($course_id);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarkastele kurssin tietoja</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Tarkastele kurssin tietoja</h1>
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

    <!-- Only for the page with GET parameter '?course-id=': -->
    <?php
    if (isset($_GET['course-id'])) {
    ?>
        <!-- The information about course: -->
        <section class="description-wrapper" id="description-wrapper">
            <h2 class="description-title">Tietoja valitusta kurssista:</h2>
            <p class="description-text">Nimi: <span class="description-value"><?php echo $course_name; ?></span></p>
            <p class="description-text">Kuvaus: <span class="description-value"><?php echo $course_description; ?></span></p>
            <p class="description-text">Kurssin alkupäivä: <span class="description-value"><?php echo $course_start_date; ?></span></p>
            <p class="description-text">Kurssin loppupäivä: <span class="description-value"><?php echo $course_end_date; ?></span></p>
            <p class="description-text">Kurssin tila: <span class="description-value"><?php echo $course_auditory; ?></span></p>
            <p class="description-text">Kurssin opettaja: <span class="description-value"><?php echo $course_teacher; ?></span></p>
            <?php
            // Check if there is at least one student who registered for this course:
            if (!empty($registered_students)) {
            ?>
                <h2 class="description-title">Kurssille ilmoittautuneet opiskelijat:</h2>
                    <table class="description-table">
                        <tr>
                            <th class="table-header">Opiskelijan nimi</th>
                            <th class="table-header">Opiskelijan vuosikurssi</th>
                        </tr>
                        <?php
                        // Run through all the entries in the array $registered_students:
                        foreach ($registered_students as $student) {
                            $fullName = $student['sukunimi'] . " " . $student["etunimi"];
                        ?>
                            <!-- If there is at least one student, show the full name and the year of the selected student: -->
                            <tr class="table-item">
                                <td class="table-column"><?php echo $fullName; ?></td>
                                <td class="table-column"><?php echo $student["vuosikurssi"]; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
            }
            // If there is no student for the selected course:
            else {
                ?>
                    <h2 class="description-title message success-message">Tälle kurssille ei ole vielä ilmoittautuneita opiskelijoita.</h2>
                    <?php
                }
                    ?>
        </section>
    <?php
    }
    ?>

    <script>
        // script to observe the option selection event:
        const selectElement = document.querySelector("select");
        selectElement.addEventListener('change', formAddressPath);

        // Function to form address path using id of selected course:
        function formAddressPath() {
            const courseId = this.value;
            if (courseId) {
                window.location.href = `get-course-info.php?course-id=${courseId}`;
            }
        }
    </script>
</body>

</html>