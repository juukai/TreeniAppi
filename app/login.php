<?php
// Aloita uusi tai jatka olemassa olevaa istuntoa
session_start();

// Tarkista, onko käyttäjä jo kirjautunut sisään ja ohjaa index.php-sivulle, jos on
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}

// Alusta muuttuja virheviestien tallentamista varten
$error = '';

// Lataa tietokantayhteyden asetukset
require 'config/db_config.php';

// Tarkista, onko lomake lähetetty POST-metodilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Tarkista tietokantayhteys
    if ($conn->connect_error) {
        die("Yhteys epäonnistui: " . $conn->connect_error);
    }

    // Poista potentiaaliset haitalliset merkit sähköpostista ja salasanasta
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Valmistele tietokantakysely käyttäjän hakemiseksi sähköpostin perusteella
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Tarkista, onko käyttäjä löytynyt ja onko salasana oikein
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Aseta istuntomuuttujat ja ohjaa käyttäjä index.php-sivulle
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['loggedin'] = true;

            header('Location: index.php');
            exit;
        } else {
            // Jos salasana on väärin, aseta virheviesti
            $error = 'Väärä salasana. Yritä uudestaan.';
        }
    } else {
        // Jos käyttäjää ei löydy annetulla sähköpostilla, aseta virheviesti
        $error = 'Ei löydy käyttäjää tällä sähköpostilla.';
    }

    // Sulje tietokantayhteys ja valmisteltu lauseke
    $stmt->close();
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
    <title>TreeniAppi - Kirjaudu</title>
</head>
<body>

<!-- Banner-kuva -->
<img src="treeniappi.jpg" alt="TreeniAppi" class="banner-image">

<!-- Kirjautumisosio -->
<div class="login-container">
    <h2>Kirjaudu</h2>
    <!-- Kirjautumislomake -->
    <form action="login.php" method="post">
        <input type="email" name="email" placeholder="Sähköposti" required>
        <input type="password" name="password" placeholder="Salasana" required>
        <input type="submit" value="Kirjaudu">
    </form>
    <!-- Rekisteröintilinkki -->
    <div class="register">
        <p>Et ole vielä jäsen?<a href="register.php"> Luo uusi tili</a></p>
    </div>
</div>

<!-- Virheviestien näyttäminen -->
<?php if(!empty($error)): ?>
<script type="text/javascript">
    alert("<?php echo $error; ?>");
</script>
<?php endif; ?>

</body>
</html>
