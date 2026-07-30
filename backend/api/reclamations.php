<?php
require_once __DIR__ . '/../config/db.php';
$user = requireLogin();
$pdo = db();
closeResolvedReclamations();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = 'SELECT r.*, u.email AS client_email, u.nom, u.prenom FROM reclamations r JOIN users u ON u.id=r.client_id';
    $params = [];
    if ($user['role'] === 'client') { $sql .= ' WHERE r.client_id = ?'; $params[] = $user['id']; }
    $sql .= ' ORDER BY r.date_creation DESC';
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $rows=$stmt->fetchAll();
    $memo=$pdo->prepare('SELECT * FROM memos WHERE reclamation_id=? ORDER BY date,id');
    $history=$pdo->prepare('SELECT * FROM status_history WHERE reclamation_id=? ORDER BY date,id');
    foreach($rows as &$row){
        $memo->execute([$row['id']]); $row['memos']=$memo->fetchAll();
        $history->execute([$row['id']]); $row['statusHistory']=$history->fetchAll();
    }
    jsonResponse(['success'=>true, 'reclamations'=>$rows]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $user['role'] !== 'client') jsonResponse(['success'=>false,'message'=>'Operation refusee'], 403);
$data = input();
if (($data['action'] ?? '') === 'delete') {
    $ref=trim((string)($data['reference'] ?? '')); $q=$pdo->prepare('SELECT id,piece_jointe FROM reclamations WHERE reference=? AND client_id=?'); $q->execute([$ref,$user['id']]); $rec=$q->fetch();
    if (!$rec) jsonResponse(['success'=>false,'message'=>'Reclamation introuvable'],404);
    $pdo->prepare('DELETE FROM reclamations WHERE id=?')->execute([$rec['id']]);
    jsonResponse(['success'=>true]);
}
foreach (['objet','detail','type','gravite','portee'] as $field) if (trim((string)($data[$field] ?? '')) === '') jsonResponse(['success'=>false,'message'=>"Champ requis: $field"], 422);

$types = ['credit_immobilier'=>'Credit immobilier','credit_auto'=>'Credit auto','credit_conso'=>'Credit consommation','credit_professionnel'=>'Credit professionnel','autre'=>'Autre'];
$gravites = ['basse'=>'Basse','moyenne'=>'Moyenne','haute'=>'Haute','critique'=>'Critique'];
if (!isset($types[$data['type']])) jsonResponse(['success'=>false,'message'=>'Type de credit invalide'],422);
if (!isset($gravites[$data['gravite']])) jsonResponse(['success'=>false,'message'=>'Niveau de gravite invalide'],422);
if (!in_array($data['portee'], ['general','dossier'], true)) jsonResponse(['success'=>false,'message'=>'Portee invalide'],422);
if ($data['portee'] === 'dossier' && trim((string)($data['numero_dossier'] ?? '')) === '') jsonResponse(['success'=>false,'message'=>'Numero de dossier requis'],422);
$piece = null;
$pieceNom = null;
if (!empty($_FILES['piece_jointe']['name'])) {
    if ($_FILES['piece_jointe']['error'] !== UPLOAD_ERR_OK || $_FILES['piece_jointe']['size'] > 5*1024*1024) jsonResponse(['success'=>false,'message'=>'Piece jointe invalide'],422);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['piece_jointe']['tmp_name']);
    $allowed = ['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
    if (!isset($allowed[$mime])) jsonResponse(['success'=>false,'message'=>'Format de fichier non autorise'],422);
    $dir = dirname(__DIR__, 2) . '/uploads'; if (!is_dir($dir)) mkdir($dir, 0775, true);
    $piece = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $pieceNom = basename((string)$_FILES['piece_jointe']['name']);
    move_uploaded_file($_FILES['piece_jointe']['tmp_name'], $dir . '/' . $piece);
}

$reference = 'REC-' . date('Y') . '-' . random_int(1000,9999);
if (($data['action'] ?? '') === 'update') {
    $ref=trim((string)($data['reference'] ?? '')); $q=$pdo->prepare('SELECT id,statut FROM reclamations WHERE reference=? AND client_id=?'); $q->execute([$ref,$user['id']]); $record=$q->fetch(); $id=(int)($record['id'] ?? 0);
    if (!$id) jsonResponse(['success'=>false,'message'=>'Reclamation introuvable'],404);
    $typeLabel = $data['type'] === 'autre' ? trim((string)($data['type_label'] ?? '')) : $types[$data['type']];
    if ($data['type'] === 'autre' && $typeLabel === '') jsonResponse(['success'=>false,'message'=>'Precisez le type de credit'],422);
    $pdo->beginTransaction();
    $fileSql = $piece ? ', piece_jointe=?, piece_jointe_nom=?' : '';
    $params = [trim($data['objet']),trim($data['detail']),$data['type'],$typeLabel,$data['gravite'],$gravites[$data['gravite']],$data['portee'],trim((string)($data['numero_dossier'] ?? '')) ?: null];
    if ($piece) { $params[] = $piece; $params[] = $pieceNom; }
    $params[] = $id;
    $pdo->prepare('UPDATE reclamations SET objet=?,detail=?,type=?,type_label=?,gravite=?,gravite_label=?,portee=?,numero_dossier=?'.$fileSql.',statut="attente",statut_label="En attente",date_modification=NOW() WHERE id=?')->execute($params);
    $pdo->prepare("DELETE FROM status_history WHERE reclamation_id=? AND status <> 'attente'")->execute([$id]);
    $pdo->prepare('INSERT INTO status_history (reclamation_id,status,action,auteur,role) VALUES (?,?,?,?,?)')->execute([$id,'attente','Reclamation modifiee, retour a En attente',$user['prenom'].' '.$user['nom'],'client']);
    $pdo->commit();
    jsonResponse(['success'=>true,'reference'=>$ref]);
}
$stmt = $pdo->prepare('INSERT INTO reclamations (reference,client_id,objet,detail,type,type_label,gravite,gravite_label,portee,numero_dossier,piece_jointe,piece_jointe_nom,statut,statut_label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"attente","En attente")');
$typeLabel = $data['type'] === 'autre' ? trim((string)($data['type_label'] ?? '')) : $types[$data['type']];
if ($data['type'] === 'autre' && $typeLabel === '') jsonResponse(['success'=>false,'message'=>'Precisez le type de credit'],422);
$stmt->execute([$reference,$user['id'],trim($data['objet']),trim($data['detail']),$data['type'],$typeLabel,$data['gravite'],$gravites[$data['gravite']],$data['portee'],trim((string)($data['numero_dossier'] ?? '')) ?: null,$piece,$pieceNom]);
$id = (int)$pdo->lastInsertId();
$pdo->prepare('INSERT INTO status_history (reclamation_id,status,action,auteur,role) VALUES (?,?,?,?,?)')->execute([$id,'attente','Reclamation enregistree - En attente','Systeme','system']);
jsonResponse(['success'=>true,'reference'=>$reference,'id'=>$id],201);
