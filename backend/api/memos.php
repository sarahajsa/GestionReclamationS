<?php
require_once __DIR__ . '/../config/db.php'; $user=requireLogin(); $pdo=db();
$data = $_SERVER['REQUEST_METHOD']==='GET' ? $_GET : input();
$id=(int)($data['reclamation_id'] ?? 0); $ref=trim((string)($data['reference'] ?? ''));
if (!$id && $ref) { $q=$pdo->prepare('SELECT id FROM reclamations WHERE reference=?'); $q->execute([$ref]); $id=(int)$q->fetchColumn(); }
$q=$pdo->prepare('SELECT client_id FROM reclamations WHERE id=?'); $q->execute([$id]); $client=(int)$q->fetchColumn();
if (!$client || ($user['role']==='client' && $client !== (int)$user['id'])) jsonResponse(['success'=>false,'message'=>'Acces refuse'],403);
if ($_SERVER['REQUEST_METHOD']==='GET') { $q=$pdo->prepare('SELECT * FROM memos WHERE reclamation_id=? ORDER BY date,id'); $q->execute([$id]); jsonResponse(['success'=>true,'memos'=>$q->fetchAll()]); }
$message=trim((string)($data['message'] ?? '')); if ($message==='') jsonResponse(['success'=>false,'message'=>'Message requis'],422);
$pdo->prepare('INSERT INTO memos (reclamation_id,auteur,role,message) VALUES (?,?,?,?)')->execute([$id,$user['prenom'].' '.$user['nom'],$user['role'],$message]);
jsonResponse(['success'=>true],201);
