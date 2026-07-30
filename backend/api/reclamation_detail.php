<?php
require_once __DIR__ . '/../config/db.php';
$user = requireLogin(); closeResolvedReclamations(); $ref = trim((string)($_GET['ref'] ?? ''));
$stmt = db()->prepare('SELECT r.*, u.email AS client_email, u.nom AS client_nom, u.prenom AS client_prenom FROM reclamations r JOIN users u ON u.id=r.client_id WHERE r.reference=?');
$stmt->execute([$ref]); $rec = $stmt->fetch();
if (!$rec || ($user['role']==='client' && (int)$rec['client_id'] !== (int)$user['id'])) jsonResponse(['success'=>false,'message'=>'Reclamation introuvable'],404);
// La consultation par l'administration démarre automatiquement le traitement.
$h=db()->prepare('SELECT * FROM status_history WHERE reclamation_id=? ORDER BY date,id'); $h->execute([$rec['id']]);
$m=db()->prepare('SELECT * FROM memos WHERE reclamation_id=? ORDER BY date,id'); $m->execute([$rec['id']]);
jsonResponse(['success'=>true,'reclamation'=>$rec,'history'=>$h->fetchAll(),'memos'=>$m->fetchAll()]);
