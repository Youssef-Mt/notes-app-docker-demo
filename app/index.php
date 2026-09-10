<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = getPdo();

// Toutes les requêtes utilisent des paramètres liés (PDO prepared statements)
// pour empêcher toute injection SQL.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' && trim($_POST['title'] ?? '') !== '') {
        $stmt = $pdo->prepare('INSERT INTO notes (title, content, created_at) VALUES (:title, :content, NOW())');
        $stmt->execute([
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content'] ?? ''),
        ]);
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM notes WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
    }
    header('Location: /');
    exit;
}

$notes = $pdo->query('SELECT id, title, content, created_at FROM notes ORDER BY created_at DESC')->fetchAll();
$hostname = gethostname();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes App - Démo monolithique Docker</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <header>
        <h1>📝 Notes App</h1>
        <p class="meta">Appli monolithique PHP + MySQL, conteneurisée — servie par le conteneur <code><?= htmlspecialchars($hostname) ?></code></p>
    </header>

    <main>
        <form method="post" class="add-form">
            <input type="hidden" name="action" value="add">
            <input type="text" name="title" placeholder="Titre" required maxlength="120">
            <textarea name="content" placeholder="Contenu" rows="3"></textarea>
            <button type="submit">Ajouter</button>
        </form>

        <ul class="notes-list">
            <?php foreach ($notes as $note): ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($note['title']) ?></strong>
                        <p><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                        <time><?= htmlspecialchars($note['created_at']) ?></time>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $note['id'] ?>">
                        <button type="submit" class="delete">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
            <?php if (!$notes): ?>
                <li class="empty">Aucune note pour le moment.</li>
            <?php endif; ?>
        </ul>
    </main>
</body>
</html>
