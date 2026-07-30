# Gestion des réclamations

Application PHP/MySQL de gestion des réclamations clients et de leur traitement par un administrateur.

## Installation avec XAMPP

1. Copier le dossier `GestionReclamationS1` dans `C:\xampp\htdocs\`.
2. Ouvrir XAMPP et démarrer **Apache** et **MySQL**.
3. Ouvrir `http://localhost/phpmyadmin`.
4. Cliquer sur **Importer**, choisir `schema.sql`, puis exécuter l'importation. La base `gestion_reclamations` apparaît ensuite dans la colonne de gauche.
5. Dans un navigateur, ouvrir `http://localhost/GestionReclamationS1/`.
6. Créer les comptes de démonstration en ouvrant une seule fois `http://localhost/GestionReclamationS1/backend/seed.php`.

## Comptes de test

- Administrateur : `admin1@test.com` / `admin123`
- Client : `client1@test.com` / `client123`

## Fonctionnalités

- Connexion sécurisée par session PHP.
- Dépôt d'une réclamation avec pièce jointe PDF/JPG/PNG (5 Mo maximum).
- Tableau de bord et suivi client avec historique des statuts.
- Tableau de bord administrateur, détail, mémos et changement de statut.
- Contrôle d'accès client/admin côté API.

Si MySQL utilise un mot de passe root, modifier `DB_PASS` dans `backend/config/db.php`.
