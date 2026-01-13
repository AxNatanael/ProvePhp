<?php
require "db.php";

if ($pdo) {
    echo "<h1> Connessione al database RIUSCITA!</h1>";
    echo "<p>Il database <b>$db</b> è connesso correttamente.</p>";
} else {
    echo "<h1> Qualcosa non va...</h1>";
}
?>