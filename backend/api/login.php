<?php
require_once __DIR__ . '/../config/db.php';
$data=input(); $email=filter_var(trim((string)($data['email']??'')),FILTER_VALIDATE_EMAIL); $password=(string)($data['password']??'');
if (!$email || $password==='') jsonResponse(['success'=>false,'message'=>'Email et mot de passe requis'],422);
$q=db()->prepare('SELECT id,email,password,role,nom,prenom,cin,telephone,numero_compte FROM users WHERE email=? LIMIT 1'); $q->execute([$email]); $user=$q->fetch();
if (!$user || !password_verify($password,$user['password'])) jsonResponse(['success'=>false,'message'=>'Identifiants incorrects'],401);
session_start(); session_regenerate_id(true); unset($user['password']); $_SESSION['user']=$user; jsonResponse(['success'=>true,'user'=>$user]);
