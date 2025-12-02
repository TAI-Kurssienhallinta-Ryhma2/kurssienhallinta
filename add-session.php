<?php
include_once 'sql-request.php';

//Get arrays with teachers, courses and auditories for filters:
$all_teachers = get_all_teachers();
$all_courses = get_all_courses();
$all_auditories = get_all_auditories();

// Store in session id from GET - päivitetty logiikka
// TÄRKEÄ: Käsittele suodattimet oikeassa järjestyksessä

// Käsittele tila
if (isset($_GET["auditory-id"])) {
    if ($_GET["auditory-id"] === "empty") {
        unset($_SESSION["auditory_id"]);
    } else {
        $_SESSION["auditory_id"] = $_GET['auditory-id'];
    }
}

// Käsittele opettaja
if (isset($_GET["teacher-id"])) {
    if ($_GET["teacher-id"] === "empty") {
        unset($_SESSION["teacher_id"]);
    } else {
        $_SESSION["teacher_id"] = $_GET['teacher-id'];
    }
}

// Käsittele kurssi viimeisenä
if (isset($_GET["course-id"])) {
    if ($_GET["course-id"] === "empty") {
        unset($_SESSION["course_id"]);
        unset($_SESSION["teacher_id"]);
        unset($_SESSION["auditory_id"]);
    } else {
        $_SESSION["course_id"] = $_GET['course-id'];
        
        // Aseta opettaja ja tila automaattisesti VAIN JOS niitä ei ole vielä sessionissa
        // TAI jos molempia ei ole asetettu manuaalisesti tässä requestissa
        $manualTeacherChange = isset($_GET["teacher-id"]);
        $manualAuditoryChange = isset($_GET["auditory-id"]);
        
        if (!$manualTeacherChange && !$manualAuditoryChange) {
            $selected_course_data = get_course_by_id($_GET["course-id"]);
            if ($selected_course_data) {
                $_SESSION["teacher_id"] = $selected_course_data["opettaja"];
                $_SESSION["auditory_id"] = $selected_course_data["tila"];
            }
        }
    }
}

// Hae aikataulutiedot suodattimien perusteella
$timetable_data = null;
if (isset($_SESSION["course_id"])) {
    $timetable_data = get_timetable_course($_SESSION["course_id"]);
} elseif (isset($_SESSION["teacher_id"])) {
    $timetable_data = get_timetable_teacher($_SESSION["teacher_id"]);
} elseif (isset($_SESSION["auditory_id"])) {
    $timetable_data = get_timetable_auditory($_SESSION["auditory_id"]);
}

$success_message = null;
$error_message = null;

// Käsittele session lisäys
if(isset($_POST["add-session"])) {
    global $conn;

    try {
        $course_id = $_POST["course_id"];
        $session_date = $_POST["session_date"]; // Tulee muodossa YYYY-MM-DD
        $start_time = $_POST["start_time"];
        $end_time = $_POST["end_time"];
        $teacher_id = $_SESSION["teacher_id"];
        $auditory_id = $_SESSION["auditory_id"];

        // Tarkista että kurssi on valittu
        if(empty($course_id)) {
            throw new Exception("Valitse ensin kurssi!");
        }

        // Tarkista että aloitusaika on ennen lopetusaikaa
        if($start_time >= $end_time) {
            throw new Exception("Aloitusajan tulee olla ennen lopetusaikaa!");
        }

        // Muunna päivämäärä oikeaan muotoon tietokantaa varten (YYYY-MM-DD)
        $date_obj = DateTime::createFromFormat('Y-m-d', $session_date);
        if (!$date_obj) {
            throw new Exception("Virheellinen päivämäärä!");
        }
        $formatted_date = $date_obj->format('Y-m-d');

        // TARKISTA PÄÄLLEKKÄISYYDET SAMALLE KURSSILLE
        $overlap_check = "SELECT COUNT(*) as count FROM aikataulu 
                         WHERE kurssi_id = :course_id 
                         AND paivamaara = :session_date
                         AND (
                             (aloitusaika < :end_time AND lopetusaika > :start_time)
                         )";
        $overlap_stmt = $conn->prepare($overlap_check);
        $overlap_stmt->execute([
            ":course_id" => $course_id,
            ":session_date" => $formatted_date,
            ":start_time" => $start_time,
            ":end_time" => $end_time
        ]);
        $overlap_result = $overlap_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($overlap_result['count'] > 0) {
            throw new Exception("Kurssilla on jo sessio tähän aikaan!");
        }

        // TARKISTA PÄÄLLEKKÄISYYDET OPETTAJALLE JA TILALLE
        // Tarkista opettajan päällekkäisyydet
        $teacher_overlap_check = "SELECT COUNT(*) as count FROM aikataulu a
                                 JOIN kurssit k ON a.kurssi_id = k.tunnus
                                 WHERE k.opettaja = :teacher_id 
                                 AND a.paivamaara = :session_date
                                 AND (
                                     (a.aloitusaika < :end_time AND a.lopetusaika > :start_time)
                                 )";
        $teacher_overlap_stmt = $conn->prepare($teacher_overlap_check);
        $teacher_overlap_stmt->execute([
            ":teacher_id" => $teacher_id,
            ":session_date" => $formatted_date,
            ":start_time" => $start_time,
            ":end_time" => $end_time
        ]);
        $teacher_overlap_result = $teacher_overlap_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($teacher_overlap_result['count'] > 0) {
            throw new Exception("Opettajalla on jo sessio tähän aikaan!");
        }

        // Tarkista tilan päällekkäisyydet
        $auditory_overlap_check = "SELECT COUNT(*) as count FROM aikataulu a
                                   JOIN kurssit k ON a.kurssi_id = k.tunnus
                                   WHERE k.tila = :auditory_id 
                                   AND a.paivamaara = :session_date
                                   AND (
                                       (a.aloitusaika < :end_time AND a.lopetusaika > :start_time)
                                   )";
        $auditory_overlap_stmt = $conn->prepare($auditory_overlap_check);
        $auditory_overlap_stmt->execute([
            ":auditory_id" => $auditory_id,
            ":session_date" => $formatted_date,
            ":start_time" => $start_time,
            ":end_time" => $end_time
        ]);
        $auditory_overlap_result = $auditory_overlap_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($auditory_overlap_result['count'] > 0) {
            throw new Exception("Tila on jo varattu tähän aikaan!");
        }

        // Hae kurssin nykyiset tiedot
        $current_course = get_course_by_id($course_id);
        
        // Päivitä kurssin opettaja ja tila jos ne ovat muuttuneet
        if($current_course["opettaja"] != $teacher_id || $current_course["tila"] != $auditory_id) {
            $update_query = "UPDATE kurssit 
                           SET opettaja = :teacher_id, tila = :auditory_id 
                           WHERE tunnus = :course_id";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([
                ":teacher_id" => $teacher_id,
                ":auditory_id" => $auditory_id,
                ":course_id" => $course_id
            ]);
        }

        // Lisää sessio aikataulu-tauluun
        $insert_query = "INSERT INTO aikataulu (kurssi_id, paivamaara, aloitusaika, lopetusaika) 
                        VALUES (:course_id, :session_date, :start_time, :end_time)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->execute([
            ":course_id" => $course_id,
            ":session_date" => $formatted_date,
            ":start_time" => $start_time,
            ":end_time" => $end_time
        ]);

        $success_message = "Sessio lisätty onnistuneesti!";
        
        // Lataa sivu uudelleen näyttämään uusi sessio
        $current_params = $_SERVER['QUERY_STRING'];
        header("Location: add-session.php?{$current_params}&success=" . urlencode($success_message));
        exit();

    } catch (Exception $e) {
        $error_message = "Virhe: " . $e->getMessage();
    } catch (PDOException $e) {
        $error_message = "Tietokantavirhe: " . htmlspecialchars($e->getMessage());
    }
}

// Success message from redirect
if(isset($_GET["success"])) {
    $success_message = $_GET["success"];
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
//  print_r($_SESSION);
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarkastele aikataulua</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .multiple-sessions {
            padding: 0 !important;
        }
        .session-container {
            display: flex;
            gap: 2px;
            height: 100%;
            width: 100%;
        }
        .session-item {
            background: rgba(0, 123, 255, 0.1);
            border: 1px solid #007bff;
            padding: 0.25rem;
            box-sizing: border-box;
            overflow: hidden;
            font-size: 0.85em;
        }
    </style>
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
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

    <!-- Session luontilomake -->
    <?php if(isset($_SESSION["course_id"]) && isset($_SESSION["teacher_id"]) && isset($_SESSION["auditory_id"])): ?>
    <div class="form-wrapper" style="max-width: 90%; width: 90%;">
        <h2 style="margin-bottom: 1rem;">Lisää uusi sessio</h2>
        
        <?php if ($success_message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="course_id" value="<?php echo $_SESSION["course_id"]; ?>">
            
            <div style="flex: 1; min-width: 200px;">
                <label for="session_date" style="display: block; margin-bottom: 0.4rem; font-weight: 700;">Valitse päivä: <span style="color:red;">*</span></label>
                <select id="session_date" name="session_date" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #ccc; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="">---Valitse päivä---</option>
                    <?php
                    // Luo vaihtoehdot valitulle viikolle (ma-pe)
                    for ($day = 0; $day < 5; $day++) {
                        $date = clone $current_date;
                        $date->modify("+{$day} days");
                        $dateValue = $date->format('Y-m-d');
                        $dateDisplay = $date->format('D d.m.Y');
                        
                        // Suomenkieliset viikonpäivät
                        $weekdays = ['Mon' => 'Ma', 'Tue' => 'Ti', 'Wed' => 'Ke', 'Thu' => 'To', 'Fri' => 'Pe'];
                        $dayName = $weekdays[$date->format('D')] ?? $date->format('D');
                        $dateDisplay = str_replace(array_keys($weekdays), array_values($weekdays), $dateDisplay);
                        
                        echo "<option value=\"{$dateValue}\">{$dateDisplay}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="start_time" style="display: block; margin-bottom: 0.4rem; font-weight: 700;">Aloitusaika: <span style="color:red;">*</span></label>
                <select id="start_time" name="start_time" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #ccc; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="">---Valitse---</option>
                    <?php
                    // Luo aloitusajat 08:00 - 15:00
                    for ($hour = 8; $hour <= 15; $hour++) {
                        $time = sprintf("%02d:00:00", $hour);
                        $timeDisplay = sprintf("%02d:00", $hour);
                        echo "<option value=\"{$time}\">{$timeDisplay}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="end_time" style="display: block; margin-bottom: 0.4rem; font-weight: 700;">Lopetusaika: <span style="color:red;">*</span></label>
                <select id="end_time" name="end_time" required style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid #ccc; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="">---Valitse---</option>
                    <?php
                    // Luo lopetusajat 09:00 - 16:00
                    for ($hour = 9; $hour <= 16; $hour++) {
                        $time = sprintf("%02d:00:00", $hour);
                        $timeDisplay = sprintf("%02d:00", $hour);
                        echo "<option value=\"{$time}\">{$timeDisplay}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="flex: 0; min-width: 150px;">
                <input type="submit" class="submit-btn" name="add-session" value="Lisää sessio" style="width: 100%; margin: 0;">
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="form-wrapper" style="max-width: 90%; width: 90%;">
        <div class="message" style="background:#fff; border:1px solid var(--line); color:var(--muted);">
            Valitse kurssi ylhäältä lisätäksesi session.
        </div>
    </div>
    <?php endif; ?>

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

    <section class="timetable-wrapper">
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
                            $isHidden = false;
                            $className = '';
                            $elementInnerText = '';
                            $elementInnerData = '';
                            $rowSpan = 1;
                            $colSpan = 1;
                            $cellStyle = '';

                            if ($column == 1) {
                                $className = "tbl-content tbl-aline-left";
                                $elementInnerText = $rowStartHour;

                            } else {
                                $className = "tbl-content";

                                // Define date for each cell in the row:
                                $elementInnerData = $d->format("d.m.y");
                                $d = DateTime::createFromFormat('d.m.y', $elementInnerData);
                                $d->modify('+1 day');

                                // Kerää kaikki sessiot tälle päivälle ja tälle ajalle
                                $sessions_at_this_time = [];
                                
                                if ($timetable_data && isset($timetable_data[1])) {
                                    foreach ($timetable_data[1] as $record) {
                                        $recordDate = DateTime::createFromFormat("d.m.Y", $record['paivamaara']);
                                        $cellDate = DateTime::createFromFormat("d.m.y", $elementInnerData);
                                        
                                        if ($recordDate && $cellDate && $recordDate->format("Y-m-d") == $cellDate->format("Y-m-d")) {
                                            $start_time = DateTime::createFromFormat("H:i:s", $record['aloitusaika'])->format("H:i");
                                            $end_time = DateTime::createFromFormat("H:i:s", $record['lopetusaika'])->format("H:i");

                                            if ($start_time == $rowStartHour) {
                                                // Tämä sessio alkaa tästä solusta
                                                $start = new DateTime($record['aloitusaika']);
                                                $end = new DateTime($record['lopetusaika']);
                                                $diff = $start->diff($end);
                                                $span = ($diff->h) * 2;
                                                
                                                $sessions_at_this_time[] = [
                                                    'record' => $record,
                                                    'rowSpan' => $span,
                                                    'start_time' => $start_time,
                                                    'end_time' => $end_time
                                                ];
                                            } elseif ($start_time < $rowStartHour && $end_time > $rowStartHour) {
                                                // Tämä solu on keskellä sessiota - piilota
                                                $isHidden = true;
                                            }
                                        }
                                    }
                                }

                                // Käsittele päällekkäiset sessiot
                                $session_count = count($sessions_at_this_time);
                                
                                if ($session_count == 1) {
                                    // Yksi sessio - täysi leveys
                                    $session = $sessions_at_this_time[0];
                                    $className = $className . " booked";
                                    $rowSpan = $session['rowSpan'];
                                    
                                    $contentParts = [];
                                    if (isset($session['record']['kurssin_nimi'])) {
                                        $contentParts[] = $session['record']['kurssin_nimi'];
                                    }
                                    if (isset($session['record']['opettajan_nimi'])) {
                                        $contentParts[] = $session['record']['opettajan_nimi'];
                                    }
                                    if (isset($session['record']['tilan_nimi'])) {
                                        $contentParts[] = $session['record']['tilan_nimi'];
                                    }
                                    $elementInnerText = implode("</br>", $contentParts);
                                    
                                } elseif ($session_count >= 2) {
                                    // Päällekkäiset sessiot - näytetään vierekkäin
                                    $className = $className . " booked multiple-sessions";
                                    $rowSpan = max(array_column($sessions_at_this_time, 'rowSpan'));
                                    
                                    $elementInnerText = '<div class="session-container">';
                                    
                                    foreach ($sessions_at_this_time as $session) {
                                        $contentParts = [];
                                        if (isset($session['record']['kurssin_nimi'])) {
                                            $contentParts[] = $session['record']['kurssin_nimi'];
                                        }
                                        if (isset($session['record']['opettajan_nimi'])) {
                                            $contentParts[] = $session['record']['opettajan_nimi'];
                                        }
                                        if (isset($session['record']['tilan_nimi'])) {
                                            $contentParts[] = $session['record']['tilan_nimi'];
                                        }
                                        
                                        $width = (100 / $session_count) . '%';
                                        $elementInnerText .= '<div class="session-item" style="width: ' . $width . ';">';
                                        $elementInnerText .= implode("</br>", $contentParts);
                                        $elementInnerText .= '</div>';
                                    }
                                    
                                    $elementInnerText .= '</div>';
                                }
                            }
                        ?>
                            <td
                                class="<?php echo $className; ?>"
                                rowspan="<?php echo $rowSpan; ?>"
                                <?php 
                                if ($isHidden == true) {
                                    ?> hidden<?php
                                }
                                ?>>
                                <?php echo $elementInnerText; ?>
                            </td>
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

        // Function to build URL with all current filters
        function buildUrlWithFilters(newParam, newValue) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            // Poista success-viesti kun suodattimia vaihdetaan
            params.delete('success');
            
            // Säilytä kaikki nykyiset parametrit sessionista
            // Lisää ne URL:iin jos niitä ei vielä ole
            <?php if (isset($_SESSION["course_id"])): ?>
            if (!params.has('course-id') && '<?php echo $_SESSION["course_id"]; ?>') {
                params.set('course-id', '<?php echo $_SESSION["course_id"]; ?>');
            }
            <?php endif; ?>
            
            <?php if (isset($_SESSION["teacher_id"])): ?>
            if (!params.has('teacher-id') && '<?php echo $_SESSION["teacher_id"]; ?>') {
                params.set('teacher-id', '<?php echo $_SESSION["teacher_id"]; ?>');
            }
            <?php endif; ?>
            
            <?php if (isset($_SESSION["auditory_id"])): ?>
            if (!params.has('auditory-id') && '<?php echo $_SESSION["auditory_id"]; ?>') {
                params.set('auditory-id', '<?php echo $_SESSION["auditory_id"]; ?>');
            }
            <?php endif; ?>
            
            // Update or add the new parameter
            if (newValue === 'empty') {
                params.delete(newParam);
            } else {
                params.set(newParam, newValue);
            }
            
            return `add-session.php?${params.toString()}`;
        }

        // Function to form address path using id of selected course:
        function formAddressPath() {
            const itemId = this.value;
            let newUrl;
            
            switch (this.id) {
                case "courses":
                    newUrl = buildUrlWithFilters('course-id', itemId);
                    break;
                case "teachers":
                    newUrl = buildUrlWithFilters('teacher-id', itemId);
                    break;
                case "auditories":
                    newUrl = buildUrlWithFilters('auditory-id', itemId);
                    break;
                case "week":
                    newUrl = buildUrlWithFilters('week', itemId);
                    break;
                default:
                    return;
            }
            
            window.location.href = newUrl;
        }
    </script>

</body>

</html>