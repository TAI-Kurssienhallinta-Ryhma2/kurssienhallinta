<?php
include_once 'sql-request.php';
require_once __DIR__ . '/utilities/utilities.php';
require_once __DIR__ . '/tree_data_structures/TreeMap.php';

// Get an array with all the auditories from the database table 'tilat':
$all_auditories = get_all_auditories();
$auditory_map = createTreeMap($all_auditories, "tunnus");

// If the GET parameter (?auditory-id=) appears in the address in the browser (after auditory's selection), then the following code is executed:
if (isset($_GET['auditory-id'])) {
    // Read the URL-get-parameter named auditory-id:
    $auditory_id = $_GET['auditory-id'];
    // Store auditory's id in SESSION:
    $_SESSION["auditory_id"] = $auditory_id;
    // Looking for the auditory with this ID in in the TreeMap data structure:
        $auditory = $auditory_map->get((int)$auditory_id);
            // Save information (name, capacity) in the variables:
            $auditory_name = $auditory['nimi'];
            $auditory_capacity = $auditory['kapasiteetti'];

    // Get an array with all registrations from the DB table "kurssikirjautumiset" for the SELECTED student:
    $reserved_courses = get_courses_by_auditory_id($auditory_id);
    //Get an associative array, if $reserved_courses is not empty, where the key is an course's id and the value is the number of registered students:
    $registered_students = calculate_registered_students_for_course($reserved_courses);

    // echo "<pre>";
    // print_r($pay_attention);
    // print_r($registered_students);
    // echo "</pre>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarkastele tilan tietoja</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.2.0/css/solid.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
<?php include 'header.php'; ?>
    <h1>Tarkastele tilan tietoja</h1>
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
                <h2 class="description-title">Kurssit, jotka pidetään tilassa <?php echo $auditory_name; ?>:</h2>
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
                        // Variable $pay_attention is boolean, by default is false.
                        // If the number of students registered for the course is greater than the capacity of the auditory,
                        // then the variable $pay_attention becomes true:
                        $pay_attention = false;
                        if (isset($auditory_capacity) && isset($students_number) && $students_number > $auditory_capacity) {
                            $pay_attention = true;
                        }

                    ?>
                        <!-- If there is at least one course, show the name, start and end date, and the name of the teacher of the selected course: -->
                        <!-- Check, if $pay_attention is true, then add additional class "pay-attention" - to highlight the row -->
                        <tr class="table-item <?php if ($pay_attention) {
                                                ?>pay-attention<?php
                                                            } ?>" id="course-<?php echo $course["tunnus"]; ?>">
                            <td class="table-column"><?php echo $course["nimi"]; ?></td>
                            <td class="table-column"><?php echo $teacher_full_name; ?></td>
                            <td class="table-column"><?php echo $course["alkupaiva"]; ?></td>
                            <td class="table-column"><?php echo $course["loppupaiva"]; ?></td>
                            <!-- Check, if $pay_attention is true, then add attention icon to the number -->
                            <td class="table-column"><?php echo $students_number; ?><?php if ($pay_attention) {
                                                                                    ?>
                                <div class="icon-wrap animate__animated animate__heartBeat">
                                    <i class="uis uis-exclamation-octagon" id="attention-icon"></i>
                                </div>
                                <div id="popup-<?php echo $course["tunnus"]; ?>" class="info-popup">The number of students (<?php echo $students_number; ?>) registered for the course "<?php echo $course["nimi"]; ?>" is greater than the capacity of the auditory (<?php echo $auditory_capacity ?>)!</div>
                            <?php
                                                                                    } ?>
                            </td>
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
                <h2 class="description-title message success-message">Valitussa tilassa ei ole tarjolla kursseja.</h2>
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

        // Function to form address path using id of selected auditory:
        function formAddressPath() {
            const auditoryId = this.value;
            if (auditoryId) {
                window.location.href = `get-auditory-info.php?auditory-id=${auditoryId}`;
            }
        }

        // script to add EventListener to attention icon:
        const attentionIcons = document.querySelectorAll(".icon-wrap");
        attentionIcons.forEach(iconElement => {
            const attentionText = iconElement.nextElementSibling;
            iconElement.addEventListener("click", () => {
                attentionText.classList.toggle("show");
            });

            // Close the attention popup window if click somewhere on the page:
            document.addEventListener('click', (event) => {
                if (!iconElement.contains(event.target) && !attentionText.contains(event.target)) {
                    attentionText.classList.remove('show');
                }
            });

        });

        // script to repeat animation of the attention icon every n-seconds:
        //Gather all elements with class "animate__heartBeat":
        const attentionWrappers = document.querySelectorAll(".animate__heartBeat");
        //set interval 5 sec for each element with class "animate__heartBeat":
        attentionWrappers.forEach(wrapper => {
            setInterval(() => {
                //first, delete class "animate__heartBeat":
                wrapper.classList.remove("animate__heartBeat");
                //forces the browser to "reread" the state of the element (called reflow), 
                //and this causes CSS to treat the re-adding of the class as a new animation:
                void wrapper.offsetWidth;
                // add class "animate__heartBeat":
                wrapper.classList.add("animate__heartBeat");
                // console.log("animate__heartBeat added");
                // delete class "animate__heartBeat" after animation is finished:
                wrapper.addEventListener('animationend', () => {
                    wrapper.classList.remove("animate__heartBeat");
                    // console.log("animate__heartBeat deleted");
                }, {
                    //so that handlers do not accumulate each time the interval is triggered, 
                    //we remove the handler after the animation has finished:
                    once: true
                });
            }, 5000);
        });

    </script>
    <?php include 'footer.php'; ?>

</body>

</html>