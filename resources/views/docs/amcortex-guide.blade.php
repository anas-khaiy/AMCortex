<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            line-height: 1.7;
            font-size: 13px;
        }

        .cover {
            background: #9A0002;
            color: white;
            padding: 55px;
            border-radius: 22px;
            margin-bottom: 35px;
        }

        .cover h1 {
            font-size: 34px;
            margin: 0;
            font-weight: bold;
        }

        .cover p {
            margin-top: 10px;
            font-size: 14px;
            opacity: .95;
        }

        h2 {
            color: #9A0002;
            font-size: 24px;
            margin-top: 35px;
            margin-bottom: 12px;
            border-bottom: 2px solid #EFE6DE;
            padding-bottom: 8px;
        }

        h3 {
            font-size: 18px;
            margin-bottom: 6px;
            color: #111827;
        }

        .box {
            background: #FAF7F4;
            border: 1px solid #EFE6DE;
            border-radius: 16px;
            padding: 18px;
            margin-top: 12px;
        }

        .grid {
            width: 100%;
        }

        .grid td {
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        ul {
            margin-top: 8px;
        }

        li {
            margin-bottom: 8px;
        }

        .important {
            background: #111827;
            color: white;
            padding: 24px;
            border-radius: 18px;
            margin-top: 30px;
        }

        .important h3 {
            color: white;
        }

        .step {
            margin-top: 20px;
            padding: 18px;
            border-left: 5px solid #9A0002;
            background: #fafafa;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            background: #9A0002;
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .table th {
            background: #9A0002;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .table td {
            border: 1px solid #EFE6DE;
            padding: 12px;
        }

        .section-text {
            margin-top: 10px;
            color: #4b5563;
        }
    </style>
</head>

<body>

{{-- COVER --}}
<div class="cover">

    <div class="badge">
        Documentation officielle
    </div>

    <h1>Guide utilisateur AMCortex</h1>

    <p>
        Plateforme intelligente de création, génération, scan et correction automatique des examens AMC.
    </p>

    <p>
        Version : 1.0
    </p>

    <p>
        AMCortex permet aux enseignants de gérer tout le cycle de correction des examens depuis une seule plateforme moderne.
    </p>

</div>

{{-- INTRO --}}
<h2>1. Présentation générale</h2>

<div class="box">
    AMCortex est une plateforme web développée pour automatiser la gestion des examens à choix multiples.
    Le système utilise Auto Multiple Choice (AMC) pour générer des copies, analyser les scans,
    détecter les réponses des étudiants et calculer automatiquement les notes.
</div>

<p class="section-text">
    Le système permet aux enseignants de :
</p>

<ul>
    <li>Créer des examens intelligents.</li>
    <li>Ajouter des questions à correction automatique.</li>
    <li>Importer des examens CSV ou JSON.</li>
    <li>Générer des copies AMC professionnelles.</li>
    <li>Scanner et analyser automatiquement les copies.</li>
    <li>Associer les copies aux étudiants.</li>
    <li>Corriger automatiquement les examens.</li>
    <li>Télécharger les copies corrigées.</li>
    <li>Consulter l’historique des corrections.</li>
</ul>

{{-- CREATE EXAM --}}
<h2>2. Création d’un examen</h2>

<div class="step">
    <h3>Étape 1 — Informations générales</h3>

    <p>
        Lors de la création d’un examen, l’enseignant doit renseigner :
    </p>

    <table class="table">
        <tr>
            <th>Champ</th>
            <th>Description</th>
        </tr>

        <tr>
            <td>Titre</td>
            <td>Nom principal affiché sur la copie d’examen.</td>
        </tr>

        <tr>
            <td>Matière</td>
            <td>Nom du cours ou du module.</td>
        </tr>

        <tr>
            <td>Durée</td>
            <td>Temps officiel de l’examen en minutes.</td>
        </tr>

        <tr>
            <td>Date</td>
            <td>Date prévue pour le contrôle.</td>
        </tr>

        <tr>
            <td>Langue</td>
            <td>Français, anglais ou arabe.</td>
        </tr>

        <tr>
            <td>Consignes</td>
            <td>Instructions affichées aux étudiants.</td>
        </tr>
    </table>
</div>

{{-- PARAMETERS --}}
<h2>3. Paramètres avancés</h2>

<table class="grid">
    <tr>
        <td>
            <div class="box">
                <h3>Copies à générer</h3>

                <p>
                    Nombre total de copies AMC créées automatiquement.
                </p>
            </div>
        </td>

        <td>
            <div class="box">
                <h3>Longueur du code étudiant</h3>

                <p>
                    Définit le nombre de chiffres à cocher pour identifier un étudiant.
                </p>
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="box">
                <h3>Mélange des questions</h3>

                <p>
                    Permet de générer différentes versions de l’examen.
                </p>
            </div>
        </td>

        <td>
            <div class="box">
                <h3>Mélange des réponses</h3>

                <p>
                    Modifie automatiquement l’ordre des choix.
                </p>
            </div>
        </td>
    </tr>
</table>

{{-- QUESTIONS --}}
<h2>4. Gestion des questions</h2>

<div class="step">
    <h3>Types de questions disponibles</h3>

    <ul>
        <li><strong>Choix unique :</strong> une seule bonne réponse.</li>
        <li><strong>Choix multiple :</strong> plusieurs réponses correctes.</li>
        <li><strong>Vrai / Faux :</strong> deux réponses uniquement.</li>
    </ul>
</div>

<div class="box">
    <h3>Règles importantes</h3>

    <ul>
        <li>Chaque question doit contenir au moins une bonne réponse.</li>
        <li>Les réponses vides doivent être évitées.</li>
        <li>Le nombre de points doit être vérifié.</li>
        <li>Les questions doivent être claires et lisibles.</li>
    </ul>
</div>

{{-- IMPORT --}}
<h2>5. Importation CSV / JSON</h2>

<div class="step">
    <h3>Import CSV</h3>

    <p>
        Le format CSV permet d’ajouter rapidement plusieurs questions.
        Le séparateur utilisé est le point-virgule <strong>;</strong>.
    </p>
</div>

<div class="step">
    <h3>Import JSON</h3>

    <p>
        Le format JSON permet une structure complète contenant :
        questions, réponses, types et bonnes réponses.
    </p>
</div>

<div class="important">
    <h3>Conseil</h3>

    Toujours vérifier les questions importées avant de générer le PDF.
</div>

{{-- PDF --}}
<h2>6. Génération du PDF AMC</h2>

<div class="box">
    Le système génère automatiquement :
    <ul>
        <li>Les questions.</li>
        <li>Les cases à cocher.</li>
        <li>Les repères AMC.</li>
        <li>Le code étudiant.</li>
        <li>Les informations nominatives.</li>
    </ul>
</div>

<div class="important">
    <h3>Important</h3>

    Les repères noirs AMC ne doivent jamais être supprimés ou modifiés après génération.
</div>

{{-- SCAN --}}
<h2>7. Analyse des scans</h2>

<div class="step">
    <h3>Étape 1 — Import des copies</h3>

    <p>
        Les copies scannées peuvent être importées au format PDF, JPG ou PNG.
    </p>
</div>

<div class="step">
    <h3>Étape 2 — Analyse AMC</h3>

    <p>
        AMC détecte automatiquement les cases cochées et les informations des copies.
    </p>
</div>

<div class="step">
    <h3>Étape 3 — Association</h3>

    <p>
        Les copies sont associées automatiquement ou manuellement aux étudiants.
    </p>
</div>

<div class="step">
    <h3>Étape 4 — Calcul des notes</h3>

    <p>
        Le système applique automatiquement le barème des questions.
    </p>
</div>

{{-- RESULTS --}}
<h2>8. Résultats et historique</h2>

<div class="box">
    Après correction, AMCortex crée une session de résultats contenant :
</div>

<ul>
    <li>Les notes des étudiants.</li>
    <li>Le fichier CSV exportable.</li>
    <li>Les copies corrigées annotées.</li>
    <li>L’historique des corrections.</li>
</ul>

<div class="important">
    <h3>Très important</h3>

    Après correction des copies, il est fortement recommandé de ne plus modifier
    l’examen ni les questions.
</div>

{{-- ARABIC --}}
<h2>9. Support de la langue arabe</h2>

<div class="box">
    AMCortex supporte les examens en arabe avec XeLaTeX et la police Amiri.
</div>

<ul>
    <li>Éviter de mélanger beaucoup de texte arabe et français dans une même question.</li>
    <li>Utiliser une police compatible Unicode.</li>
    <li>Vérifier l’aperçu avant génération.</li>
</ul>

{{-- ADMIN --}}
<h2>10. Administration</h2>

<div class="box">
    L’administrateur peut :
</div>

<ul>
    <li>Gérer les enseignants.</li>
    <li>Consulter les statistiques globales.</li>
    <li>Voir les examens générés.</li>
    <li>Consulter les étudiants.</li>
    <li>Analyser les statistiques AMCortex.</li>
</ul>

{{-- INSTALLATION --}}
<h2>11. Installation et dépendances système</h2>

<div class="box">
    AMCortex utilise plusieurs outils externes afin d’assurer la génération et la correction automatiques des examens AMC.
</div>

<p class="section-text">
    Selon l’environnement utilisé, certaines dépendances doivent être installées sur la machine exécutant le système :
</p>

<table class="table">
    <tr>
        <th>Composant</th>
        <th>Rôle</th>
    </tr>

    <tr>
        <td>Auto-Multiple-Choice (AMC)</td>
        <td>Génération et correction automatique des examens.</td>
    </tr>

    <tr>
        <td>XeLaTeX</td>
        <td>Compilation des examens PDF, notamment pour l’arabe.</td>
    </tr>

    <tr>
        <td>Python</td>
        <td>Traitement IA et recherche sémantique.</td>
    </tr>

    <tr>
        <td>Poppler</td>
        <td>Conversion des PDF scannés en images.</td>
    </tr>

    <tr>
        <td>SQLite</td>
        <td>Gestion des fichiers internes AMC.</td>
    </tr>
</table>

<div class="important">
    <h3>Installation automatique recommandée</h3>

    <p>
        Afin de simplifier le déploiement du système, AMCortex peut utiliser
        un script d’installation automatique permettant d’installer les dépendances nécessaires
        telles que AMC, Python, XeLaTeX et les bibliothèques de traitement des examens.
    </p>

    <p>
        Cette approche facilite l’installation de la plateforme et réduit les manipulations techniques nécessaires.
    </p>
</div>

<div class="step">
    <h3>Important</h3>

    <p>
        Dans une architecture professionnelle, les dépendances AMC et IA sont généralement installées côté serveur.
        Les enseignants utilisent alors uniquement l’interface web sans installation locale supplémentaire.
    </p>
</div>

{{-- SECURITY --}}
<h2>12. Sécurité et recommandations</h2>

<div class="important">
    <h3>Bonnes pratiques</h3>

    <ul>
        <li>Faire des scans propres et droits.</li>
        <li>Toujours sauvegarder les résultats.</li>
        <li>Ne jamais modifier un examen après correction.</li>
        <li>Vérifier les bonnes réponses avant génération.</li>
        <li>Conserver l’historique des sessions.</li>
    </ul>
</div>

{{-- FOOTER --}}
<div class="footer">
    AMCortex — Guide utilisateur officiel<br>
    Génération et correction intelligente des examens
</div>

</body>
</html>