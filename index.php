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
                $info=analyserMETAR($metar_brut);
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

function analyserMETAR($metar){
    //Analyser le METAR et retourner une description lisible
    //Cette fonction est un exemple très basique et ne couvre pas tous les cas possibles
    $mots = explode(" ", $metar);
    // Résultat :
    // $mots[0] est "LFPG"
    // $mots[1] est "221300Z"
    // $mots[2] est "24010KT"
    die($metar);
    $aeroport = $mots[0];
    $date = transformeDate($mots[1]);
    if($mots[2]!="AUTO"){
        $vent = transformeVent($mots[2]);
    } else {
        $vent = transformeVent($mots[3]);
    }
    $temp = recupTemperature($metar);




    return 1; 



}



function recupTemperature($metar){
    $result = "";
    //Exemple d'analyse pour la température
    if (preg_match("/\s(M?\d{2})\/(M?\d{2})\s/", $metar, $matches)) {
        $temp = str_replace('M', '-', $matches[1]);
        $dewpoint = str_replace('M', '-', $matches[2]);
        $temp = intval($temp);
        $dewpoint = intval($dewpoint);
        $result .= "Température: $temp °C, Point de rosée: $dewpoint °C\n";
    }
    //Ajouter d'autres analyses pour le vent, la visibilité, etc.
    return nl2br($result);
}


function transformeDate($date_str){
    //Exemple de transformation de la date du METAR
    //221300Z -> 22 du mois à 13h00 UTC
    if (preg_match("/(\d{2})(\d{2})(\d{2})Z/", $date_str, $matches)) {
        $day = $matches[1];
        $hour = $matches[2];
        $minute = $matches[3];
        return "Le $day à $hour:$minute UTC";
    }
    return "Date inconnue";
}


function transformeVent($vent_str){
    //Exemple de transformation du vent du METAR
    //24010KT -> Vent de 240° à 10 nœuds
    if (preg_match("/(\d{3})(\d{2})KT/", $vent_str, $matches)) {
        $direction = $matches[1];
        $vitesse = $matches[2];
        return "Vent de $direction ° à $vitesse nœuds";
    }
    return "Vent inconnu";
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
        <h3>METAR :</h3>
        <div class="metar-box">
            <?php echo $metar_brut; ?>
        </div>
        <?php if($info){
            //Si il y a des infos on affiche sinon non 
            echo'
            <h3>Informations :</h3>
            <div class="metar-box">
                <?php echo "Aéroport : $aeroport"; ?>
                <?php echo "Date : $date"; ?>
                <?php echo "Vent : $vent"; ?>
                <?php echo "Température : $temp"; ?>
            </div>';
        }
        ?>
        
        <?php endif; ?>
</div>

</body>
</html>