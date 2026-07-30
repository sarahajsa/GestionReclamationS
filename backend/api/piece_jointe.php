<?php
require_once __DIR__ . '/../config/db.php';
$user = requireLogin();
$ref = trim((string)($_GET['ref'] ?? ''));
$stmt = db()->prepare('SELECT r.piece_jointe, r.piece_jointe_nom, r.client_id FROM reclamations r WHERE r.reference=?');
$stmt->execute([$ref]);
$file = $stmt->fetch();
if (!$file || ($user['role'] === 'client' && (int)$file['client_id'] !== (int)$user['id']) || empty($file['piece_jointe'])) { http_response_code(404); exit('Fichier introuvable'); }
$path = dirname(__DIR__, 2) . '/uploads/' . basename($file['piece_jointe']);
if (!is_file($path)) { http_response_code(404); exit('Fichier introuvable'); }
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
$name = $file['piece_jointe_nom'] ?: basename($file['piece_jointe']);
$disposition = ($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header("Content-Disposition: {$disposition}; filename*=UTF-8''" . rawurlencode($name));
readfile($path);
