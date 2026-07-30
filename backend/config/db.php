<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'gestion_reclamations';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("ALTER TABLE reclamations MODIFY statut ENUM('nouveau','encours','attente','prise_charge','demande_supplementaire','resolu','cloture','rejete') NOT NULL DEFAULT 'attente'");
        $pdo->exec("UPDATE reclamations SET statut='attente', statut_label='En attente' WHERE statut='nouveau'");
        $pdo->exec("UPDATE status_history SET status='attente', action='Réclamation enregistrée - En attente' WHERE status='nouveau'");
        $pdo->exec("ALTER TABLE reclamations MODIFY statut ENUM('attente','encours','prise_charge','demande_supplementaire','resolu','cloture') NOT NULL DEFAULT 'attente'");
        try { $pdo->exec("ALTER TABLE reclamations ADD COLUMN piece_jointe_nom VARCHAR(255) NULL AFTER piece_jointe"); } catch (PDOException $e) { /* colonne déjà présente */ }
        $pdo->exec("CREATE TABLE IF NOT EXISTS statuts (id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(40) NOT NULL UNIQUE, label VARCHAR(80) NOT NULL) ENGINE=InnoDB");
        $pdo->exec("INSERT IGNORE INTO statuts (code,label) VALUES ('attente','En attente'),('encours','En cours'),('prise_charge','Prise en charge'),('demande_supplementaire','Demande supplémentaire'),('resolu','Résolue'),('cloture','Clôturée')");
    }
    return $pdo;
}

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function input(): array
{
    $raw = file_get_contents('php://input');
    $json = $raw ? json_decode($raw, true) : null;
    return is_array($json) ? $json : $_POST;
}

function requireLogin(?string $role = null): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $user = $_SESSION['user'] ?? null;
    if (!$user) jsonResponse(['success' => false, 'message' => 'Non authentifie'], 401);
    if ($role && ($user['role'] ?? '') !== $role) jsonResponse(['success' => false, 'message' => 'Acces refuse'], 403);
    return $user;
}

function statusLabel(string $status): string
{
    return ['attente'=>'En attente','encours'=>'En cours','prise_charge'=>'Prise en charge','demande_supplementaire'=>'Demande supplémentaire','resolu'=>'Résolue','cloture'=>'Clôturée'][$status] ?? $status;
}

function closeResolvedReclamations(): void
{
    $pdo = db();
    $rows = $pdo->query("SELECT id FROM reclamations WHERE statut='resolu' AND date_modification <= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchAll(PDO::FETCH_COLUMN);
    if (!$rows) return;
    $update = $pdo->prepare("UPDATE reclamations SET statut='cloture', statut_label='Cloture', date_modification=NOW() WHERE id=? AND statut='resolu'");
    $history = $pdo->prepare("INSERT INTO status_history (reclamation_id,status,action,auteur,role) VALUES (?,'cloture','Cloture automatique après 7 jours sans intervention ou message','Système','system')");
    foreach ($rows as $id) { $update->execute([(int)$id]); if ($update->rowCount()) $history->execute([(int)$id]); }
}
