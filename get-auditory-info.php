<?php
include_once 'sql-request.php';

// Get an array with all the auditories from the database table 'tilat':
$all_auditories = get_all_auditories();

// If the GET parameter (?auditory-id=) appears in the address in the browser (after auditory's selection), then the following code is executed:
if (isset($_GET['auditory-id'])) {
    // Read the URL-get-parameter named auditory-id:
    $auditory_id = $_GET['auditory-id'];
    // Store auditory's id in SESSION:
    $_SESSION["auditory_id"] = $auditory_id;
    // Looking for the auditory with this ID in stored array with all auditories:
    foreach ($all_auditories as $auditory) {
        if ($auditory['tunnus'] == $auditory_id) {
            // Save information (name, capacity) in the variables:
            $auditory_name = $auditory['nimi'];
            $auditory_capacity = $auditory['kapasiteetti'];
            break;
        }
    }

    // Get an array with all registrations from the DB table "kurssikirjautumiset" for the SELECTED student:
    $reserved_courses = get_courses_by_auditory_id($auditory_id);
    $registered_students = calculate_registered_students_for_course($reserved_courses);
    // echo "<pre>";
    // print_r($registered_students);
    // echo "</pre>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nähdä tilan tiedot</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Nähdä tilan tiedot</h1>
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

    <!-- Only for the page with GET parameter '?auditory-id=': -->
    <?php
    if (isset($_GET['auditory-id'])) {
    ?>
        <!-- The information about auditory: -->
        <section class="description-wrapper" id="description-wrapper">
            <h2 class="description-title">Tietoja valitusta tilasta:</h2>
            <p class="description-text">Nimi: <span class="description-value"><?php echo $auditory_name; ?></span></p>
            <p class="description-text">Kapasiteetti: <span class="description-value"><?php echo $auditory_capacity; ?></span></p>
            <?php
            // Check if there is at least one course reserved for the selected auditory :
            if (!empty($reserved_courses)) {
            ?>
                <h2 class="description-text">Kurssit, jotka pidetään tilassa <?php echo $auditory_name; ?>:</p>
                    <table class="description-table">
                        <tr>
                            <th class="table-header">Kurssinimi</th>
                            <th class="table-header">Vastaava opettaja</th>
                            <th class="table-header">Kurssin alkupäivä</th>
                            <th class="table-header">Kurssin loppupäivä</th>
                            <th class="table-header">Osallistujien määrä</th>
                        </tr>
                        <?php
                        // Run through all the entries in the array $reserved_courses:
                        foreach ($reserved_courses as $course) {
                            $teacher_full_name = $course["sukunimi"] . " " . $course["etunimi"];
                            $students_number = $registered_students[$course["tunnus"]];

                        ?>
                            <!-- If there is at least one course, show the name, start and end date, and the name of the teacher of the selected course: -->
                            <tr class="table-item" id="course-<?php echo $course["tunnus"]; ?>">
                                <td class="table-column"><?php echo $course["nimi"]; ?></td>
                                <td class="table-column"><?php echo $teacher_full_name; ?></td>
                                <td class="table-column"><?php echo $course["alkupaiva"]; ?></td>
                                <td class="table-column"><?php echo $course["loppupaiva"]; ?></td>
                                <td class="table-column"><?php echo $students_number; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
            }
            // If there is no course for the selected auditory:
            else {
                ?>
                    <h2 class="description-text">Valitussa tilassa ei ole tarjolla kursseja.</p>
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

        // Function to form address path using id of selected auditory:
        function formAddressPath() {
            const auditoryId = this.value;
            if (auditoryId) {
                window.location.href = `get-auditory-info.php?auditory-id=${auditoryId}`;
            }
        }

        // <?php
        // if (!empty($auditory_capacity) && isset($students_number) && $students_number > $auditory_capacity) {
        // ?>
        //     console.log("More students have registered for this course than the auditory can accommodate.", $students_number);
        // <?php
        // } else {
        // ?>
        //     console.log("It's OK", $students_number);
        // <?php
        // }
        // ?>

    </script>

</body>

</html>