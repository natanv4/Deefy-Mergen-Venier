<?php

declare(strict_types=1);

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\Action;
use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\AddPlaylistAction;

class Dispatcher {
    protected ?string $action = null;

    public function __construct() {
        $this->action = $_GET['action'] ?? 'default';
    }

    public function run(): void {
        switch ($this->action) {
            case 'add-playlist':
                $action = new AddPlaylistAction();
                break;

            case 'default':
            default:
                $action = new DefaultAction();
                break;
        }

        $content = $action->execute();
        $this->renderPage($content);
    }

    private function renderPage(string $html): void {
        $playlistCouranteNom = isset($_SESSION['current_playlist']) ? htmlspecialchars($_SESSION['current_playlist']->nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : null;

        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deefy - Gestionnaire de Playlists</title>
    <style>
        :root {
            --primary: #1db954;
            --primary-dark: #1aa34a;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --card-hover: #282828;
            --text-color: #ffffff;
            --text-muted: #b3b3b3;
            --border-color: #333333;
            --error-bg: #4a1515;
            --error-border: #e74c3c;
            --success-bg: #153b21;
            --success-border: #2ecc71;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: #000000;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .brand {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 1.2rem;
            align-items: center;
            flex-wrap: wrap;
        }
        nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }
        nav a:hover {
            color: var(--text-color);
        }
        .current-badge {
            font-size: 0.85rem;
            background-color: var(--card-bg);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            color: var(--primary);
            font-weight: 600;
        }
        main {
            flex: 1;
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .card, .welcome-box {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            margin-bottom: 1.5rem;
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        h3 {
            font-size: 1.2rem;
            margin: 1.2rem 0 0.6rem;
            color: var(--text-color);
        }
        p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        ul, ol {
            margin-left: 1.5rem;
            color: var(--text-muted);
            line-height: 1.8;
        }
        .home-actions {
            margin: 1.5rem 0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background-color: #333333;
            color: #ffffff;
            border-radius: 24px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s ease, transform 0.1s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #444444;
            transform: translateY(-1px);
        }
        .btn-primary {
            background-color: var(--primary);
            color: #000000;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            color: #000000;
        }
        
        /* Alertes */
        .alert {
            padding: 1rem 1.2rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-success {
            background-color: var(--success-bg);
            border: 1px solid var(--success-border);
            color: #a3e9b8;
        }
        .alert-error {
            background-color: var(--error-bg);
            border: 1px solid var(--error-border);
            color: #f8b4b4;
        }
        .badge-session {
            font-size: 0.8rem;
            background-color: #282828;
            color: var(--primary);
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            border: 1px solid #3e3e3e;
        }

        /* Formulaires */
        .form-card {
            max-width: 650px;
            margin: 0 auto;
        }
        .styled-form .form-group {
            margin-bottom: 1.2rem;
        }
        .styled-form label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            color: var(--text-color);
            font-weight: 500;
        }
        .styled-form input[type="text"],
        .styled-form input[type="number"],
        .styled-form select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: #121212;
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .styled-form input:focus,
        .styled-form select:focus {
            border-color: var(--primary);
        }
        .styled-form input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            background-color: #121212;
            color: var(--text-muted);
            border-radius: 6px;
            border: 1px dashed var(--border-color);
        }
        .styled-form .form-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Listes et pistes */
        .audiolist-details h2 {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.6rem;
            margin-bottom: 1.2rem;
        }
        .tracks-ol {
            list-style-position: inside;
        }
        .track-item {
            padding: 0.8rem 0;
            border-bottom: 1px solid #282828;
        }
        .track-item:last-child {
            border-bottom: none;
        }
        .track-compact {
            display: inline-block;
            vertical-align: middle;
            width: 95%;
        }
        .audio-player audio {
            margin-top: 0.5rem;
            height: 32px;
            width: 100%;
            max-width: 450px;
        }
        .audiolist-summary {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Grille Mes Playlists */
        .playlists-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .playlist-card {
            background-color: #242424;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1rem;
            transition: background-color 0.2s ease, transform 0.15s ease;
        }
        .playlist-card:hover {
            background-color: var(--card-hover);
            transform: translateY(-2px);
        }
        .playlist-card h3 a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .playlist-card h3 a:hover {
            color: var(--primary);
        }
        .playlist-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
            margin-bottom: 0;
        }

        footer {
            background-color: #000000;
            color: var(--text-muted);
            text-align: center;
            padding: 1.2rem;
            font-size: 0.85rem;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="brand">🎧 Deefy</a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="?action=playlist-list">Mes playlists</a></li>
                <li><a href="?action=add-playlist">Créer une playlist</a></li>
                <li><a href="?action=display-playlist">Playlist courante</a></li>
HTML;

        if ($playlistCouranteNom !== null) {
            echo "<li><a href=\"?action=display-playlist\"><span class=\"current-badge\">▶ {$playlistCouranteNom}</span></a></li>";
        }

        echo <<<HTML
            </ul>
        </nav>
    </header>
    <main>
        {$html}
    </main>
    <footer>
        <p>&copy; 2026 Deefy - IUT Nancy-Charlemagne - BUT Informatique S3</p>
    </footer>
</body>
</html>
HTML;
    }
}
