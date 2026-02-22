<?php
// On initialise les variables pour éviter les erreurs d'affichage
$metar_brut = "";
$error = "";

if (isset($_POST['oaci'])) {
    $oaci = strtoupper(trim($_POST['oaci']));
    
    // Validation basique (4 caractères alpha)
    if (strlen($oaci) === 4 && ctype_alpha($oaci)) {
        $url = "https://tgftp.nws.noaa.gov/data/observations/metar/stations/{$oaci}.TXT";

        // Récupération avec gestion d'erreur
        $raw_data = @file_get_contents($url);

        if ($raw_data !== FALSE) {
            $lines = explode("\n", trim($raw_data));
            if (isset($lines[1])) {
                $metar_brut = $lines[1];
            } else {
                $error = "Format de fichier invalide pour $oaci.";
            }
        } else {
            $error = "Impossible de trouver la météo pour le code $oaci.";
        }
    } else {
        $error = "Veuillez entrer un code OACI valide (ex: LFPG).";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Décodeur METAR</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #e0e0e0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #1e1e1e; padding: 20px; border-radius: 8px; }
        input { padding: 10px; border-radius: 4px; border: none; width: 150px; }
        button { padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .metar-box { background: #000; color: #00ff00; padding: 15px; font-family: monospace; border-left: 4px solid #007bff; margin-top: 20px; }
        .error { color: #ff6b6b; margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Décodeur METAR</h1>
    <form method="post">
        <input type="text" name="oaci" placeholder="Ex: LFPG" maxlength="4" required>
        <button type="submit">Récupérer</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($metar_brut): ?>
        <h3>Données brutes :</h3>
        <div class="metar-box">
            <?php echo $metar_brut; ?>
        </div>
        
        <?php endif; ?>
</div>

</body>
</html>