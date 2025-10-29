<?php
include_once 'sql-request.php';

// Get an array with all the teachers from the database table 'opettajat':
$all_teachers = get_all_teachers();

// If the GET parameter (?teacher-id=) appears in the address in the browser (after teacher's selection), then the following code is executed:
if (isset($_GET['teacher-id'])) {
    // Read the URL-get-parameter named teacher-id:
    $teacher_id = $_GET['teacher-id'];
    // Store teacher's id in SESSION:
    $_SESSION["teacher_id"] = $teacher_id;
    // Looking for the teacher with this ID in stored array with all teachers:
    foreach ($all_teachers as $teacher) {
        if ($teacher['tunnusnumero'] == $teacher_id) {
            // Save information (name, surname, subject) in the variables:
            $teacher_name = $teacher['etunimi'];
            $teacher_surname = $teacher['sukunimi'];
            $teacher_subject = $teacher['aine'];
            break;
        }
    }

    // Get an array with all registrations from the DB table "kurssikirjautumiset" for the SELECTED student:
    $teacher_courses = get_teachers_course($teacher_id);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nähdä opettajan tiedot</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Nähdä opettajan tiedot</h1>
    <label for="teachers">Valitse opettaja:</label>
    <!-- Create list of all teachers from the DB table "opettajat": -->
    <select id="teachers" name="teaches">
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

    <!-- Only for the page with GET parameter '?teacher-id=': -->
    <?php
    if (isset($_GET['teacher-id'])) {
    ?>
        <!-- The information about teacher: -->
        <section class="description-wrapper" id="description-wrapper">
            <h2 class="description-title">Tietoja valitusta opettajasta:</h2>
            <p class="description-text">Etunimi: <span class="description-value"><?php echo $teacher_name; ?></span></p>
            <p class="description-text">Sukunimi: <span class="description-value"><?php echo $teacher_surname; ?></span></p>
            <p class="description-text">Aine: <span class="description-value"><?php echo $teacher_subject; ?></span></p>
            <?php
            // Check if there is at least one course for which the teacher is responsible.:
            if (!empty($teacher_courses)) {
            ?>
                <h2 class="description-title">Tietoja opettajan kursseista:</h2>
                    <table class="description-table">
                        <tr>
                            <th class="table-header">Kurssinimi</th>
                            <th class="table-header">Kurssin alkupäivä</th>
                            <th class="table-header">Kurssin loppupäivä</th>
                            <th class="table-header">Tila</th>
                        </tr>
                        <?php
                        // Run through all the entries in the array $teacher_courses:
                        foreach ($teacher_courses as $course) {
                        ?>
                            <!-- If there is at least one course, show the name, start and end date, and the name of the auditory of the selected course: -->
                            <tr class="table-item">
                                <td class="table-column"><?php echo $course["nimi"]; ?></td>
                                <td class="table-column"><?php echo $course["alkupaiva"]; ?></td>
                                <td class="table-column"><?php echo $course["loppupaiva"]; ?></td>
                                <td class="table-column"><?php echo $course["tila"]; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
            }
            // If there is no course for the selected teacher:
            else {
                ?>
                    <h2 class="description-title message success-message">Ei ole kursseja, joista opettaja olisi vastuussa.</h2>
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
        console.log("select element is ", selectElement);
        selectElement.addEventListener('change', formAddressPath);

        // Function to form address path using id of selected teacher:
        function formAddressPath() {
            const teachId = this.value;
            console.log("Choosed id is ", teachId);
            if (teachId) {
                window.location.href = `get-teacher-info.php?teacher-id=${teachId}`;
            }
        }
    </script>
</body>

</html>