CREATE DATABASE IF NOT EXISTS gestion_reclamations CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_reclamations;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reclamation_messages, memos, status_history, reclamations, statuts, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('client','admin') NOT NULL DEFAULT 'client',
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  cin VARCHAR(30) NULL,
  telephone VARCHAR(30) NULL,
  numero_compte VARCHAR(50) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE statuts (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  label VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

INSERT INTO statuts (code, label) VALUES
('attente', 'En attente'),
('encours', 'En cours'),
('prise_charge', 'Prise en charge'),
('demande_supplementaire', 'Demande supplémentaire'),
('resolu', 'Résolue'),
('cloture', 'Clôturée');

CREATE TABLE reclamations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(40) NOT NULL UNIQUE,
  client_id INT UNSIGNED NOT NULL,
  objet VARCHAR(255) NOT NULL,
  detail TEXT NOT NULL,
  type VARCHAR(80) NOT NULL,
  type_label VARCHAR(120) NOT NULL,
  gravite ENUM('basse','moyenne','haute','critique') NOT NULL,
  gravite_label VARCHAR(60) NOT NULL,
  portee ENUM('general','dossier') NOT NULL,
  numero_dossier VARCHAR(100) NULL,
  piece_jointe VARCHAR(255) NULL,
  piece_jointe_nom VARCHAR(255) NULL,
  statut ENUM('attente','encours','prise_charge','demande_supplementaire','resolu','cloture') NOT NULL DEFAULT 'attente',
  statut_label VARCHAR(60) NOT NULL DEFAULT 'En attente',
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reclamation_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_reclamations_client (client_id), INDEX idx_reclamations_statut (statut)
) ENGINE=InnoDB;

CREATE TABLE status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL, action VARCHAR(255) NOT NULL,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  auteur VARCHAR(200) NOT NULL, role VARCHAR(30) NOT NULL,
  CONSTRAINT fk_history_reclamation FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE memos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT UNSIGNED NOT NULL,
  auteur VARCHAR(200) NOT NULL, role VARCHAR(30) NOT NULL,
  message TEXT NOT NULL, date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_memo_reclamation FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reclamation_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reclamation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL, auteur VARCHAR(200) NOT NULL, role VARCHAR(20) NOT NULL,
  message TEXT NOT NULL, date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_messages_reclamation (reclamation_id),
  CONSTRAINT fk_message_reclamation FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE
) ENGINE=InnoDB;
