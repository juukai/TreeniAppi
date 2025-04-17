<?php
// Asetetaan aikavyöhyke
date_default_timezone_set('Europe/Helsinki');

// Virheiden näyttö päälle (hyödyllistä kehitysvaiheessa)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Käynnistetään sessio
session_start();

// Tietokantayhteyden asetukset (tiedosto 'config/db_config.php')
require 'config/db_config.php';

// Uloskirjautumisen käsittely
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Tarkistetaan onko käyttäjä kirjautunut sisään
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Asetetaan käyttäjätunnus ja muita muuttujia
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Käyttäjä';
$bodyParts = ['Hauis', 'Ojentaja', 'Rinta', 'Olkapäät', 'Selkä', 'Jalat', 'Peppu', 'Pohkeet', 'Vatsat'];

// Noudetaan mahdollinen sessioviesti ja poistetaan se käytön jälkeen
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Treenin tallennus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finish'])) {
    $workouts = [];
    // Tarkistetaan lomakkeen tiedot
    foreach ($_POST['bodyPart'] as $index => $bodyPart) {
        if (in_array($bodyPart, $bodyParts) && !empty($_POST['exercise'][$index]) && $_POST['weight'][$index] > 0 && $_POST['reps'][$index] > 0) {
            $workouts[] = [
                'bodyPart' => $bodyPart,
                'title' => $_POST['exercise'][$index],
                'weight' => $_POST['weight'][$index],
                'reps' => $_POST['reps'][$index]
            ];
        }
    }

    // Tietokantaan tallennus
    if (!empty($workouts)) {
        $workout_data = json_encode($workouts);
        $hash = hash('sha256', $workout_data);
        $prev_hash = '';

        // Haetaan edellinen hash tietokannasta ketjutusta varten
        $stmt = $conn->prepare("SELECT hash FROM workouts WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($prev_hash);
        $stmt->fetch();
        $stmt->close();

        $timestamp = date('Y-m-d H:i:s');

        // Tallennetaan uusi treeni
        $insert_stmt = $conn->prepare("INSERT INTO workouts (user_id, workout_data, previous_hash, hash, timestamp) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param('issss', $userId, $workout_data, $prev_hash, $hash, $timestamp);
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = 'Uusi treeni tallennettu onnistuneesti!';
        } else {
            $_SESSION['message'] = "Virhe tallennettaessa treeniä: " . $conn->error;
        }
        $insert_stmt->close();
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['message'] = 'Syötetiedot ovat virheelliset.';
        header("Location: index.php");
        exit;
    }
}

// Aiempien treenien hakeminen
$userWorkouts = [];
$stmt = $conn->prepare("SELECT workout_data, timestamp FROM workouts WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userWorkouts[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenipäiväkirja Etusivu</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Pään sisältökontaineri -->
<div class="container">
    <!-- Tervetuloteksti -->
    <div class="welcome">
        <h1>Hyvää treeniä <span class="username"><?php echo htmlspecialchars($username); ?></span>!</h1>
        <!-- Uloskirjautumisnappula -->
        <form action="index.php" method="post" class="logout-form">
            <input type="submit" name="logout" value="Kirjaudu ulos" class="logout-button">
        </form>
    </div>
    <!-- Lomake treenien tallentamista varten -->
    <form action="index.php" method="post">
        <!-- Näytetään mahdollinen ilmoitusviesti käyttäjälle -->
        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <div id="workout-sets">
            <div class="workout-set">
                <label for="bodyPart">Lihasryhmä:</label>
                <select name="bodyPart[]" onchange="updateExercises(this)" required>
                    <option value="">Valitse lihasryhmä...</option>
                    <option value="Hauis">Hauis</option>
                    <option value="Ojentaja">Ojentaja</option>
                    <option value="Rinta">Rinta</option>
                    <option value="Olkapäät">Olkapäät</option>
                    <option value="Selkä">Selkä</option>
                    <option value="Jalat">Jalat</option>
                    <option value="Peppu">Peppu</option>
                    <option value="Pohkeet">Pohkeet</option>
                    <option value="Vatsat">Vatsat</option>
                </select>
                <label for="exercise">Harjoitus:</label>
                <select name="exercise[]" required>
                    <option value="">Valitse harjoitus...</option>
                </select>
                <label for="weight">Paino (kg):</label>
                <input type="number" name="weight[]" min="0" required>
                <label for="reps">Toistot:</label>
                <input type="number" name="reps[]" min="0" required>
            </div>
        </div>
        <!-- Lisää sarja ja tallenna treeni -napit -->
        <div class="button-container">
            <button type="button" onclick="addWorkoutSet()">Lisää Sarja</button>
            <input type="submit" name="finish" value="Tallenna Treeni">
        </div>
    </form>
    <!-- Aiempien treenien näyttäminen -->
    <div class="user-workouts">
        <h2>Aiemmat Treenisi</h2>
        <ul>
            <?php foreach ($userWorkouts as $workout): ?>
                <li>
                    <strong>Päivämäärä:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($workout['timestamp']))); ?><br>
                    <strong>Treeni:</strong>
                    <?php
                    // Dekoodataan JSON-muodossa oleva treenidata
                    $workoutData = json_decode($workout['workout_data'], true);
                    if ($workoutData) {
                        // Käydään läpi jokainen harjoitus
                        foreach ($workoutData as $exercise) {
                            echo htmlspecialchars($exercise['bodyPart'] . ": " . $exercise['title'] . ", " . $exercise['weight'] . " kg, " . $exercise['reps'] . " toistoa") . "<br>";
                        }
                    } else {
                        echo 'Virhe treenidatan tulostamisessa.';
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- JavaScript-koodi -->
<script src="js/app.js"></script>

</body>
</html>