<?php
include_once 'sql-request.php';

// Get an array with all the students from the database:
$all_students = get_all_students();

// If the GET parameter (?student-id=) appears in the address in the browser (after student's selection), then the following code is executed:
if (isset($_GET['student-id'])) {
    // Read the URL-get-parameter named student-id:
    $student_id = $_GET['student-id'];
    // Store student's id in SESSION:
    $_SESSION["student_id"] = $student_id;
    // Looking for the student with this ID in stored array with all students:
    foreach ($all_students as $student) {
        // var_dump($all_students[0]['opiskelijanumero']);
        if ($student['opiskelijanumero'] == $student_id) {
            // Save information (name, surname, birthday and vuosikurssi) in the variables:
            $student_name = $student['etunimi'];
            $student_surname = $student['sukunimi'];
            $student_birthday = $student['syntymapaiva'];
            $student_grade = $student['vuosikurssi'];
            break;
        }
    }

    // Get an array with all registrations from the DB table "kurssikirjautumiset" for the SELECTED student:
    $student_registrations = get_student_registrations($student_id);
    // echo "<pre>";
    // print_r($student_registrations);
    // echo "</pre>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nähdä opiskelijan tiedot</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Nähdä opiskelijan tiedot</h1>
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

    <!-- Only for the page with GET parameter '?student-id=': -->
    <?php
    if (isset($_GET['student-id'])) {
    ?>
        <!-- The information about student: -->
        <section class="description-wrapper" id="description-wrapper">
            <h2 class="description-title">Tietoja valitusta opiskelijasta:</h2>
            <p class="description-text">Etunimi: <span class="description-value"><?php echo $student_name; ?></span></p>
            <p class="description-text">Sukunimi: <span class="description-value"><?php echo $student_surname; ?></span></p>
            <p class="description-text">Syntymäpäivä: <span class="description-value"><?php echo $student_birthday; ?></span></p>
            <p class="description-text">Vuosikurssi: <span class="description-value"><?php echo $student_grade; ?></span></p>
            <?php
            // Check if the student has at least one registration for the course:
            if (!empty($student_registrations)) {
            ?>
                <h2 class="description-text">Tietoja valituista kursseista:</h2>
                <table class="description-table">
                    <tr>
                        <th class="table-header">Kurssin alkupäivä</th>
                        <th class="table-header">Kurssinimi</th>
                    </tr>
                    <?php
                        // Run through all the entries in the array $student_registrations:
                        foreach ($student_registrations as $registration) {
                        ?>
                            <!-- If there is at least one registration, show the date and the name of the course: -->
                            <tr class="table-item">
                                <td class="table-column"><?php echo $registration["alkupaiva"]; ?></td>
                                <td class="table-column"><?php echo $registration["nimi"]; ?></td>
                        </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
            }
            // If there is no registration for the selected student:
            else {
                ?>
                    <h2 class="description-text">Opiskelija ei ole vielä valinnut kursseja.</h2>
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

        // Function to form address path using id of selected student:
        function formAddressPath() {
            const stId = this.value;
            if (stId) {
                window.location.href = `get-student-info.php?student-id=${stId}`;
            }
        }
    </script>
</body>

</html>