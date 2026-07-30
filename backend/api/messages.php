<?php
require_once __DIR__ . '/../config/db.php';
$user=requireLogin();$pdo=db();
$pdo->exec("CREATE TABLE IF NOT EXISTS reclamation_messages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,reclamation_id INT UNSIGNED NOT NULL,user_id INT UNSIGNED NOT NULL,auteur VARCHAR(200) NOT NULL,role VARCHAR(20) NOT NULL,message TEXT NOT NULL,date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX idx_messages_reclamation (reclamation_id),CONSTRAINT fk_message_reclamation FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE) ENGINE=InnoDB");
$data=$_SERVER['REQUEST_METHOD']==='GET'?$_GET:input();$ref=trim((string)($data['reference']??''));$q=$pdo->prepare('SELECT id,client_id FROM reclamations WHERE reference=?');$q->execute([$ref]);$rec=$q->fetch();
if(!$rec||($user['role']==='client'&&(int)$rec['client_id']!==(int)$user['id']))jsonResponse(['success'=>false,'message'=>'Acces refuse'],403);
if($_SERVER['REQUEST_METHOD']==='GET'){$q=$pdo->prepare('SELECT id,auteur,role,message,date FROM reclamation_messages WHERE reclamation_id=? ORDER BY date,id');$q->execute([$rec['id']]);jsonResponse(['success'=>true,'messages'=>$q->fetchAll()]);}
$message=trim((string)($data['message']??''));if($message==='')jsonResponse(['success'=>false,'message'=>'Message requis'],422);
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO reclamation_messages (reclamation_id,user_id,auteur,role,message) VALUES (?,?,?,?,?)')->execute([$rec['id'],$user['id'],$user['prenom'].' '.$user['nom'],$user['role'],$message]);
    if ($user['role']==='admin') {
        $q=$pdo->prepare("UPDATE reclamations SET statut='prise_charge',statut_label='Prise en charge',date_modification=NOW() WHERE id=? AND statut IN ('attente','encours')"); $q->execute([$rec['id']]);
        if ($q->rowCount()) $pdo->prepare("INSERT INTO status_history (reclamation_id,status,action,auteur,role) VALUES (?,'prise_charge','Réponse administrative - prise en charge automatique','Systeme','system')")->execute([$rec['id']]);
    } else $pdo->prepare('UPDATE reclamations SET date_modification=NOW() WHERE id=?')->execute([$rec['id']]);
    $pdo->commit(); jsonResponse(['success'=>true],201);
} catch (Throwable $e) { $pdo->rollBack(); jsonResponse(['success'=>false,'message'=>'Erreur serveur'],500); }
