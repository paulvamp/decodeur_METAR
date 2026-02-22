<?php


/*
A faire : 
- Faire tempo et BECMG
- Mettre la pluis et la neige dans la partie "Informations" (actuellement on ne les affiche pas)
- Ajouter la pression (Q1013 ou A2992) dans les informations
- 

*/




$grands_aeroports = [
    // Nord (Hauts-de-France)
    "LFQQ" => "LFQQ - Lille Lesquin",
    "LFAC" => "LFAC - Calais Dunkerque",
    "LFBK" => "LFBK - Saint-Quentin Roupy",
    // Grandes Villes & Hubs
    "LFPG" => "LFPG - Paris Charles de Gaulle",
    "LFPO" => "LFPO - Paris Orly",
    "LFPB" => "LFPB - Le Bourget",
    "LFMN" => "LFMN - Nice Côte d'Azur",
    "LFLL" => "LFLL - Lyon Saint-Exupéry",
    "LFML" => "LFML - Marseille Provence",
    "LFBO" => "LFBO - Toulouse Blagnac",
    "LFBD" => "LFBD - Bordeaux Mérignac",
    "LFRS" => "LFRS - Nantes Atlantique",
    "LFST" => "LFST - Strasbourg Entzheim",
    "LFMT" => "LFMT - Montpellier Méditerranée",
    // Capitales proches
    "EBBR" => "EBBR - Bruxelles National",
    "LSGG" => "LSGG - Genève Cointrin",
    "EGLL" => "EGLL - Londres Heathrow",
    "LEMD" => "LEMD - Madrid Barajas"
];

$metar_brut = "";
$error = "";

if (isset($_POST['oaci'])) {
    $oaci = strtoupper(trim($_POST['oaci']));
    
    if (strlen($oaci) === 4 && ctype_alpha($oaci)) {
        // Optionnel : si l'utilisateur a tapé LFQL, on redirige vers LFQQ pour la NOAA
        $station_to_fetch = ($oaci == "LFQL") ? "LFQQ" : $oaci;
        
        $url = "https://tgftp.nws.noaa.gov/data/observations/metar/stations/{$station_to_fetch}.TXT";
        $raw_data = @file_get_contents($url);

        if ($raw_data !== FALSE) {
            $lines = explode("\n", trim($raw_data));
            if (isset($lines[1])) {
                $metar_brut = $lines[1];
            }
        } else {
            $error = "Station météo introuvable pour $oaci.";
        }
    } else {
        $error = "Veuillez entrer un code OACI valide.";
    }
}

function analyserMETAR($metar){
    $grands_aeroports = [
        // Nord (Hauts-de-France)
        "LFQQ" => "Lille Lesquin",
        "LFAC" => "Calais Dunkerque",
        "LFBK" => "Saint-Quentin Roupy",
        // Grandes Villes & Hubs
        "LFPG" => "Paris Charles de Gaulle",
        "LFPO" => "Paris Orly",
        "LFPB" => "Le Bourget",
        "LFMN" => "Nice Côte d'Azur",
        "LFLL" => "Lyon Saint-Exupéry",
        "LFML" => "Marseille Provence",
        "LFBO" => "Toulouse Blagnac",
        "LFBD" => "Bordeaux Mérignac",
        "LFRS" => "Nantes Atlantique",
        "LFST" => "Strasbourg Entzheim",
        "LFMT" => "Montpellier Méditerranée",
        // Capitales proches
        "EBBR" => "Bruxelles National",
        "LSGG" => "Genève Cointrin",
        "EGLL" => "Londres Heathrow",
        "LEMD" => "Madrid Barajas"
    ];
    //Analyser le METAR et retourner une description lisible
    //Cette fonction est un exemple très basique et ne couvre pas tous les cas possibles
    $mots = explode(" ", $metar);
    $phi=0; //Dephasage si il y a "AUTO" ou pas dans le METAR
    // Résultat :
    // $mots[0] est "LFPG"
    // $mots[1] est "221300Z"
    // $mots[2] est "24010KT"
    //die($metar);
    $aeroport = $mots[0];
    $date = transformeDate($mots[1]);
    $auto=0;
    if($mots[2]!="AUTO"){
        //die($mots[2]);
        //On verifie si après il y a du vent variable 
        if(isset($mots[3]) && preg_match("/(\d{3})V(\d{3})/", $mots[3])){
            $vent = transformeVent($mots[2]." ".$mots[3]);
            $phi+=1;
        } else {
            $vent = transformeVent($mots[2]);
        }
        //$vent = transformeVent($mots[2]);
    } else {
        //die($mots[2]."//".$mots[3]."//".$mots[4]);
        //$vent = transformeVent($mots[3]);
        if(isset($mots[4]) && preg_match("/(\d{3})V(\d{3})/", $mots[4])){
            $vent = transformeVent($mots[3]." ".$mots[4]);
            $phi+=1;
        } else {
            $vent = transformeVent($mots[3]);
        }
        $auto=1; 
    }
    $nuages = rechercheNuages($metar);
    $visibilite=rechercheVisi($metar);
    if(preg_match("/CAVOK/", $visibilite)){
        $visibilite="10 km ou plus";
        $nuages="Pas de nuages significatifs";
    }else{
        $visibilite.=" m"; 
    }

    $temp = recupTemperature($metar);

    $pression=rechercherQNH($metar);







    echo'<h3>Informations :</h3> <div class="info-box">';
        if($auto){
            echo "Automatique<br>";
        }
        echo "<strong>Aéroport :</strong> $aeroport ($grands_aeroports[$aeroport])<br>";
        echo "<strong>Date :</strong> $date <br>";
        echo "<strong>Vent :</strong> $vent <br>";
        echo "<strong>Température :</strong> $temp <br>";
        echo "<strong>Visibilité :</strong> $visibilite<br>";
        echo "<strong>Nuages :</strong> $nuages <br>";
        echo "<strong>QNH :</strong> $pression <br>";
    echo '</div>';

}



function recupTemperature($metar){
    $result = "";
    //Exemple d'analyse pour la température
    if (preg_match("/\s(M?\d{2})\/(M?\d{2})\s/", $metar, $matches)) {
        $temp = str_replace('M', '-', $matches[1]);
        $dewpoint = str_replace('M', '-', $matches[2]);
        $temp = intval($temp);
        $dewpoint = intval($dewpoint);
        $result .= "$temp °C \n<strong>Point de rosée </strong>: $dewpoint °C";
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
        $heure_locale = ($hour + 1) % 24; // Ajout d'une heure pour l'heure locale (simplification)
        return "Le $day à $hour:$minute UTC ($heure_locale:$minute heure locale)";
    }
    return "Date inconnue";
}


function transformeVent($vent_str){
    //Exemple de transformation du vent du METAR
    //24010KT -> Vent de 240° à 10 nœuds
    if ($vent_str == "/////KT") return "Calme";
    $dephasage=0;
    global $V;
    $res="";
    if (preg_match("/([0-9]{3}|VRB)(\d{2})(G\d{2})?KT\s?((\d{3})V(\d{3}))?/", $vent_str, $matches)) {
        if($matches[1]=="VRB"){
            $direction="Variable";
            $res="Vent variable";
        } else {
            $direction = $matches[1];
            $res="$direction °";
        }
        
        $vitesse = intval($matches[2]); 
        $vitesse_kmh = round($vitesse * 1.852);
        $res.=" à $vitesse noeuds ($vitesse_kmh km/h)";
        if(!empty($matches[3])){
            $rafale = str_replace('G', '', $matches[3]);
            $res.= " (rafale à $rafale KT)"; 
        }
        $V=0;
        if (!empty($matches[5]) && !empty($matches[6])) {
            $direction_min= $matches[5];
            $direction_max=$matches[6]; 
            $varia_txt = "<br>Vent variable entre {$matches[5]}° et {$matches[6]}°";
            $res.=$varia_txt;
            $V=1;
        }
        return $res;
    }
    return "Vent inconnu (Brut: $vent_str)";
}


function rechercheNuages($metar){
    //Exemple de recherche de nuages dans le METAR
    //FEW020 -> Quelques nuages à 2000 pieds
    $result = "";
    if (preg_match_all("/(FEW|SCT|BKN|OVC)(\d{3})/", $metar, $matches)) {
        //print_r($matches);
        /*
        Array(
            [0] => Array(
                [0] => OVC012
            )
            [1] => Array(
                [0] => OVC
            )
            [2] => Array(
                [0] => 012
            )
        )
        */
        for ($i = 0; $i < count($matches[0]); $i++) {    
            $type = $matches[1][$i];
            $altitude = intval($matches[2][$i]) * 100; // Convertir en pieds
            $result .= "<br>";
            switch ($type) {
                case "FEW": $result .= "Quelques nuages (1-2/8) à "; break;
                case "SCT": $result .= "Nuages épars (3-4/8) à "; break;
                case "BKN": $result .= "Ciel couvert (5-7/8) à "; break;
                case "OVC": $result .= "Ciel complètement couvert (8/8) à "; break;
            }
            $result .= "$altitude pieds";
        }
        return nl2br($result);
    }
    return "Pas de nuages significatifs";
}


function rechercheVisi($metar){
    if (preg_match_all("/\s(CAVOK|9999|\d{4})\s/", $metar, $matches)) {
        return $matches[0][0];
    }
}



function rechercherQNH($metar){
    if (preg_match_all("/Q(\d{4})/", $metar, $matches)) {
        return str_replace('Q', '', $matches[0][0]);
    }




}















?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Décodeur METAR Pro</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #e0e0e0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #1e1e1e; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        input { padding: 12px; border-radius: 6px; border: 1px solid #333; width: 200px; background: #2c2c2c; color: white; }
        button { padding: 12px 20px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0056b3; }
        .metar-box { background: #000; color: #00ff44; padding: 15px; font-family: 'Courier New', monospace; border-left: 4px solid #007bff; margin-top: 20px; }
        .info-box { background: #1a1a1a; color: #fbff00; padding: 15px; border-radius: 6px; border: 1px solid #333; margin-top: 15px; line-height: 1.6; }
        .error { color: #ff6b6b; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>✈️ Décodeur METAR</h1>
    
    <form method="post">
        <input 
            type="text" 
            name="oaci" 
            placeholder="Ex: LF..." 
            maxlength="4" 
            list="liste-oaci" 
            required 
            autocomplete="off"
            value="<?php echo isset($oaci) ? $oaci : ''; ?>"
        >
        
        <datalist id="liste-oaci">
            <?php foreach ($grands_aeroports as $code => $nom): ?>
                <option value="<?php echo $code; ?>"><?php echo $nom; ?></option>
            <?php endforeach; ?>
        </datalist>
        
        <button type="submit">Décoder</button>
    </form>

    <?php if ($error): ?>
        <p class="error">❌ <?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($metar_brut): ?>
        <div class="metar-box">
            <strong>BRUT :</strong><br>
            <?php echo $metar_brut; ?>
        </div>
        <?php analyserMETAR($metar_brut); ?>
    <?php endif; ?>
</div>

</body>
</html>