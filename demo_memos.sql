USE gestion_reclamations;

-- Données de démonstration : 4 mémos pour chacune des 2 premières réclamations.
-- Le test NOT EXISTS évite de créer des doublons si le script est relancé.
INSERT INTO memos (reclamation_id, auteur, role, message)
SELECT r.id, 'Administration', 'admin', 'Votre reclamation a bien ete prise en compte.'
FROM (SELECT id FROM reclamations ORDER BY id LIMIT 2) r
WHERE NOT EXISTS (SELECT 1 FROM memos m WHERE m.reclamation_id=r.id AND m.message='Votre reclamation a bien ete prise en compte.');

INSERT INTO memos (reclamation_id, auteur, role, message)
SELECT r.id, 'Agence', 'agence', 'Le dossier est en cours de verification par nos equipes.'
FROM (SELECT id FROM reclamations ORDER BY id LIMIT 2) r
WHERE NOT EXISTS (SELECT 1 FROM memos m WHERE m.reclamation_id=r.id AND m.message='Le dossier est en cours de verification par nos equipes.');

INSERT INTO memos (reclamation_id, auteur, role, message)
SELECT r.id, 'Administration', 'admin', 'Une verification complementaire des informations est en cours.'
FROM (SELECT id FROM reclamations ORDER BY id LIMIT 2) r
WHERE NOT EXISTS (SELECT 1 FROM memos m WHERE m.reclamation_id=r.id AND m.message='Une verification complementaire des informations est en cours.');

INSERT INTO memos (reclamation_id, auteur, role, message)
SELECT r.id, 'Agence', 'agence', 'Nous vous informerons des que le traitement sera finalise.'
FROM (SELECT id FROM reclamations ORDER BY id LIMIT 2) r
WHERE NOT EXISTS (SELECT 1 FROM memos m WHERE m.reclamation_id=r.id AND m.message='Nous vous informerons des que le traitement sera finalise.');
