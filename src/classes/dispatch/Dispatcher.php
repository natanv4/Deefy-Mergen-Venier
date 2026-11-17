<?php

declare(strict_types=1);

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\Action;
use iutnc\deefy\action\DefaultAction;

class Dispatcher {
    protected ?string $action = null;

    public function __construct() {
        $this->action = $_GET['action'] ?? 'default';
    }

    public function run(): void {
        switch ($this->action) {
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
            --text-color: #ffffff;
            --text-muted: #b3b3b3;
            --border-color: #282828;
            --accent: #2e77d0;
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
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            color: var(--primary);
        }
        main {
            flex: 1;
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .welcome-box, .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        h3 {
            font-size: 1.2rem;
            margin: 1.5rem 0 0.8rem;
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
        }
        .track-compact {
            padding: 0.5rem 0;
        }
        .audio-player audio {
            margin-top: 0.4rem;
            height: 32px;
            width: 100%;
            max-width: 400px;
        }
        footer {
            background-color: #000000;
            color: var(--text-muted);
            text-align: center;
            padding: 1.2rem;
            font-size: 0.85rem;
            border-top: 1px solid var(--border-color);
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
            echo "<li><span class=\"current-badge\">▶ {$playlistCouranteNom}</span></li>";
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
