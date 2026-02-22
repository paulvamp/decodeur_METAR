typedef struct {
    char oaci[5];          // Code de l'aéroport (ex: LFPG)
    int jour;              // Jour du mois
    int heure;             // Heure UTC
    int minute;            // Minutes
    int vent_dir;          // Direction du vent en degrés
    int vent_vitesse;      // Vitesse du vent
    char vent_unite[3];    // KT (nœuds) ou MPS (mètres/seconde)
    int visibilite;        // Visibilité en mètres (9999 = >10km)
    int temperature;       // Température en Celsius
    int point_rosee;       // Point de rosée
    int qnh;               // Pression atmosphérique (hPa)
    char conditions[100];  // Texte brut pour les nuages/phénomènes
} METAR;




