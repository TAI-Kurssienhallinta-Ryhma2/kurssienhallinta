<?php
include_once 'sql-request.php';

// Set the maximum number of records to be shown in a single page:
$limit = 10;


// Define the total amount of records (registrations) in the table 'kurssikirjautumiset':
// We will use this info to define the amount of pages for pagination:
$total_records = count_regestrations(get_registrations(0, null));
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

//Get arrays with students, teachers, courses and auditories for filters:
$all_students = get_all_students();
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();


// Form the array of records depending on the selected filters:
if (isset($_GET["auditory-id"]) && $_GET["auditory-id"] !== null) {
    $_SESSION["auditory_id"] = $_GET['auditory-id'];
    $registration_portion = get_registrations(0, null, ['selected_auditory_id' => (int)$_GET['auditory-id']]);
    $total_records = count_regestrations($registration_portion);
    $total_pages = ceil($total_records / $limit);
} elseif (isset($_GET["student-id"]) && $_GET["student-id"] !== null) {
    $_SESSION["student_id"] = $_GET['student-id'];
    $registration_portion = get_registrations(0, null, ['selected_student_id' => (int)$_GET['student-id']]);
    $total_records = count_regestrations($registration_portion);
    $total_pages = ceil($total_records / $limit);
} elseif (isset($_GET["teacher-id"]) && $_GET["teacher-id"] !== null) {
    $_SESSION["teacher_id"] = $_GET['teacher-id'];
    $registration_portion = get_registrations(0, null, ['selected_teacher_id' => (int)$_GET['teacher-id']]);
    $total_records = count_regestrations($registration_portion);
    $total_pages = ceil($total_records / $limit);
} elseif (isset($_GET["course-id"]) && $_GET["course-id"] !== null) {
    $_SESSION["course_id"] = $_GET['course-id'];
    $registration_portion = get_registrations(0, null, ['selected_course_id' => (int)$_GET['course-id']]);
    $total_records = count_regestrations($registration_portion);
    $total_pages = ceil($total_records / $limit);
} else {
    //Get an array from the DB from table 'kurssikirjautumiset' starting from index $start_from and limiting up to $limit records 
    // and store in the $registration_portion variable:
    $registration_portion = get_registrations($start_from, $limit);
}


$success_message = null;
$error_message = null;

/* DELETE STUDENT LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete-reg'])) {
    $ids = $_POST['delete'] ?? [];
    echo "<pre>";
    print_r($ids);
    echo "</pre>";

    if (empty($ids)) {
        header("Location: edit-delete-registration.php?success=error");
        exit();
    } else {
        global $conn;
        $conn->beginTransaction(); // Start transaction (delete registrations)

        try {
            foreach ($ids as $id) {
                if ($id === '' || !ctype_digit($id)) {
                    throw new Exception("Virheellinen kurssikirjautuminen tunnus.");
                }

                // Delete registration:
                $delReg = $conn->prepare("DELETE FROM kurssikirjautumiset WHERE tunnus = :id");
                $delReg->bindParam(':id', $id, PDO::PARAM_INT);
                $delReg->execute();
            }
            $conn->commit();
            // After delete - go back without selected registration
            header("Location: edit-delete-registration.php?success=deleted");
            exit();
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $error_message = "Poistovirhe: " . htmlspecialchars($e->getMessage());
        }
    }
}

/* READ SUCCESS MESSAGES FROM URL */
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') $success_message = "Kurssikirjautumisen tiedot päivitetty onnistuneesti!";
    if ($_GET['success'] === 'deleted') $success_message = "Kurssikirjautuminen poistettu onnistuneesti!";
    if ($_GET['success'] === 'error') $success_message = "Valitse vähintään yksi poistettava kohde.";
}

echo "<pre>";
// print_r($registration_portion);
// print_r($total_records);
// print_r($total_pages);
echo "</pre>";

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

    <!-- Section with message -->
    <?php if (isset($success_message) ?? isset($error_message)) {
    ?>
        <div class="form-wrapper">
            <?php if ($success_message): ?>
                <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="message error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </div>
    <?php
    } ?>


    <!-- Section with data-table -->
    <form action="" method="POST">
        <div class="data-wrapper">
            <h2>Kurssikirjautumiset</h2>



            <?php

            if (!empty($registration_portion)) {

            ?>
                <table class="description-table">

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
                                <td class="table-column" colspan="3">Kurssi <b>&laquo<?php echo $course_name; ?>&raquo</b> (opettaja <b><?php echo $teacher_fullname; ?></b>, tila <b><?php echo $auditory_name; ?></b>)</td>
                                <td class="table-column-center">
                                    <input type="checkbox" id="del-course-<?php echo $course_id; ?>" name="delete" value="delete-<?php echo $course_id; ?>" data-course="<?php echo $course_id; ?>" data-group="grouped">
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
                                <input type="checkbox" id="del-registration-<?php echo $registration["registrationId"]; ?>" name="delete[]" value="<?php echo $registration["registrationId"]; ?>" data-course="<?php echo $course_id; ?>" data-regid="<?php echo $registration["registrationId"]; ?>">
                            </td>
                            <td class="table-column-center">
                                <input type="checkbox" id="edit-registration-<?php echo $registration["registrationId"]; ?>" name="edit[]" value="edit-<?php echo $registration["registrationId"]; ?>">
                            </td>

                        </tr>
                    <?php
                    }
                    ?>
                </table>
        </div>
        <?php
            } else { //if there is no records in table "kurssikirjautumiset" for selected filters:
                if (isset($_GET["auditory-id"])) {
        ?>
            <h2 class="description-title message success-message">Valitulle tilalle ei ole kurssiilmoittautumisia.</h2>
        <?php
                } elseif (isset($_GET["student-id"])) {
        ?>
            <h2 class="description-title message success-message">Opiskelija ei ole vielä ilmoittautunut millekään kurssille.</h2>
        <?php
                } elseif (isset($_GET["teacher-id"])) {
        ?>
            <h2 class="description-title message success-message">Kukaan ei ole vielä rekisteröitynyt opettajan luokse.</h2>
        <?php
                } elseif (isset($_GET["course-id"])) {
        ?>
            <h2 class="description-title message success-message">Kurssille ei ole ilmoittautumista.</h2>
    <?php
                }
            }
    ?>

    <!-- Section with pagination -->
    <?php
    if (!isset($_GET["auditory-id"]) && !isset($_GET["student-id"]) && !isset($_GET["teacher-id"]) && !isset($_GET["course-id"])) {
    ?>
        <div class="pagination-wrapper">
            <ul class="pagination-list">
                <?php
                // K is assumed to be the middle index.
                $k = (($pn + 2 > $total_pages) ? $total_pages - 2 : (($pn - 2 < 1) ? 3 : $pn));

                //initialize a variable to form a string that will contain the pagination buttons:
                $pagLink = "";

                //form the First Page (<<) and Previous Page (<) buttons, provided that the page number is greater than or equal to 2:
                if ($pn >= 2) {
                    $pagLink .= "<li class='pagination-list-item pagination-list-item-marginal'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=1'>&laquo</a></li>";
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

                //form the Last Page (>>) and Next Page (>) buttons, provided that the page number is greater than or equal to 2:
                if ($pn < $total_pages) {
                    $pagLink .=  "<li class='pagination-list-item'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . ($pn + 1) . "'> Next </a></li>";
                    $pagLink .=  "<li class='pagination-list-item pagination-list-item-marginal'><a class='pagination-list-item-link' href='edit-delete-registration.php?page=" . $total_pages . "'>&raquo</a></li>";
                }
                echo $pagLink;
                ?>
            </ul>
        </div>
    <?php
    }
    ?>

    <!-- Section with button -->
    <div <?php if (empty($registration_portion)) { ?>hidden <?php } ?> class="button-wrapp">
        <div class="inner-wrapper">
            <button type="submit" name="update-reg" class="submit-btn">Tallenna muutokset</button>
            <button type="submit" name="delete-reg" class="submit-btn"
                onclick="return confirm('Haluatko varmasti poistaa tämän kurssikirjautumisen?');">
                Poista kurssinkirjautuminen
            </button>
        </div>

    </div>
    </form>


    <!-- Scripts -->
    <script>
        // script to observe the option selection event for courses:
        const selectElements = document.querySelectorAll("select");
        selectElements.forEach(selectElement => {
            selectElement.addEventListener('change', formAddressPath);
        });

        // console.log(selectElements);

        // Function to form address path using id of selected course:
        function formAddressPath() {
            const itemId = this.value;
            // console.log("this is", this);
            // console.log("this.id is", this.id);
            // console.log("itemId is", itemId);
            switch (this.id) {
                case "courses":
                    window.location.href = `edit-delete-registration.php?course-id=${itemId}`;

                    break;
                case "students":
                    window.location.href = `edit-delete-registration.php?student-id=${itemId}`;
                    break;
                case "teachers":
                    window.location.href = `edit-delete-registration.php?teacher-id=${itemId}`;
                    break;
                case "auditories":
                    window.location.href = `edit-delete-registration.php?auditory-id=${itemId}`;
                    break;
                default:
                    break;
            }
        }

        // add eventListener to the checkbox "delete record"
        const checkboxElements = document.querySelectorAll("input[type='checkbox']");
        checkboxElements.forEach(checkboxElement => {
            // console.log(checkboxElement);
            checkboxElement.addEventListener("click", handleCheckedMark);
        });


        function handleCheckedMark() {
            //Store the type (delete or edit) of the clicked checkbox:
            const typeOfSelectedCheckbox = this.name;

            //Get all input[type='checkbox'] on the page:
            const checkboxElements = document.querySelectorAll("input[type='checkbox']");
            //For each input[type='checkbox']:
            checkboxElements.forEach(checkboxElement => {
                //Define the type (delete or edit) for each element of input type=checkbox:
                const typeOfCheckElement = checkboxElement.name;

                if (typeOfSelectedCheckbox == "edit[]") {
                    if (typeOfCheckElement == "delete" || typeOfCheckElement == "delete[]") {
                        //Unchecked all before checked elements:
                        checkboxElement.checked = false;
                    }
                } else {
                    if (typeOfCheckElement == "edit[]") {
                        //Unchecked all before checked elements:
                        checkboxElement.checked = false;
                    }
                }
            });

            if (typeOfSelectedCheckbox == 'delete[]' || typeOfSelectedCheckbox == 'delete') {
                let isChecked = this.checked;
                let isGrouped = this.hasAttribute('data-group');
                if (isGrouped == true) {
                    let course_id = this.getAttribute('data-course');
                    console.log("Click on grouped line for course ", course_id);
                    let all_courses_registration = document.getElementsByName('delete[]');
                    all_courses_registration.forEach(registration => {
                        if (registration.getAttribute('data-course') == course_id) {
                            registration.checked = isChecked;
                        }
                    });
                }
            } else {
                console.log("Click on edit checkbox:", typeOfSelectedCheckbox);
            }
        }

    </script>
</body>

</html>