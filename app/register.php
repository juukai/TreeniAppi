<?php
// Alusta uusi tai jatka olemassa olevaa istuntoa
session_start();

// Tarkista, onko käyttäjä jo kirjautunut sisään; jos on, ohjaa hänet index.php-sivulle
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

// Lataa tietokannan konfiguraatiotiedosto
require 'config/db_config.php';

// Alusta muuttuja rekisteröitymisvirheille
$register_error = '';

// Tarkista, onko lomake lähetetty POST-pyynnöllä
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Poista whitespace käyttäjän syöttämästä datasta
    $form_username = trim($_POST['username']);
    $form_email = trim($_POST['email']);
    $form_password = trim($_POST['password']);
    $password_hash = password_hash($form_password, PASSWORD_DEFAULT); // Luo salasanahash
    
    // Luo yhteys tietokantaan
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Tarkista, onnistuiko yhteys
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Valmista SQL-lause sähköpostin tarkistamiseksi
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $form_email);
    $stmt->execute();
    $stmt->store_result();
    
    // Tarkista, onko sähköpostiosoite jo käytössä
    if ($stmt->num_rows > 0) {
        $register_error = 'Käyttäjä tällä sähköpostilla löytyy jo.';
    } else {
        // Lisää uusi käyttäjä tietokantaan
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $form_username, $form_email, $password_hash);
        if ($stmt->execute()) {
            // Onnistunut rekisteröityminen, ohjaa kirjautumissivulle
            header("Location: login.php");
            exit;
        } else {
            $register_error = 'Something went wrong. Please try again later.';
        }
    }
    
    // Sulje lause
    $stmt->close();
    // Sulje tietokantayhteys
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
    <title>Tee uusi tunnus</title>
</head>
<body>

<!-- Banner-kuva -->
<img src="luotunnuksesi.jpg" alt="LuoTunnusTreeniAppiin" class="banner-image">

<!-- Rekisteröintiosio -->
<div class="register-container">
    <h2>Luo uusi tili</h2>
    <!-- Rekisteröintilomake -->
    <form action="register.php" method="post">
        <input type="text" name="username" placeholder="Käyttäjätunnus" required>
        <input type="email" name="email" placeholder="Sähköposti" required>
        <input type="password" name="password" placeholder="Salasana" required>
        <input type="submit" value="Luo tunnus">
        
        <!-- Näytä virheviesti, jos sellainen on olemassa -->
        <?php if ($register_error != ''): ?>
        <p class="error"><?php echo $register_error; ?></p>
        <?php endif; ?>
    </form>
    
    <!-- Linkki takaisin kirjautumissivulle -->
    <div class="login-link">
        <a href="login.php">Takaisin kirjautumiseen</a>
    </div>
</div>

</body>
</html>