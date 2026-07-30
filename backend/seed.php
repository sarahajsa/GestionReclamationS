<?php
require __DIR__ . '/config/db.php';
$pdo=db();
try {
  $pdo->beginTransaction();
  $q=$pdo->prepare('INSERT INTO users(email,password,role,nom,prenom,cin,telephone,numero_compte) VALUES(?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE password=VALUES(password),role=VALUES(role),nom=VALUES(nom),prenom=VALUES(prenom),cin=VALUES(cin),telephone=VALUES(telephone),numero_compte=VALUES(numero_compte)');
  $q->execute(['admin1@test.com',password_hash('admin123',PASSWORD_DEFAULT),'admin','Admin','1',null,null,null]);
  $q->execute(['client1@test.com',password_hash('client123',PASSWORD_DEFAULT),'client','Client','1','AB123456','0612345678','00123456789012345678']);
  $pdo->commit(); echo "Comptes de test crees.\nAdmin: admin1@test.com / admin123\nClient: client1@test.com / client123\n";
} catch (Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); http_response_code(500); echo 'Seed impossible: '.$e->getMessage(); }
