# Notes App — démo monolithique Docker (EC06)

Petite application monolithique (PHP 8.2 + MySQL 8) créée pour prouver la
maîtrise de la conteneurisation et de l'orchestration Docker, indépendamment
du projet SkillHub — conformément à la consigne de l'épreuve EC06.

## Architecture

- **app** (conteneur unique) : PHP-Apache, sert le CRUD de notes (`app/`),
  se connecte à MySQL via PDO (requêtes préparées).
- **db** : MySQL 8, schéma initialisé automatiquement via `db-init/init.sql`.
- Les deux services sont orchestrés par `docker-compose.yml` : réseau dédié,
  volumes nommés, health checks, politique de redémarrage
  `unless-stopped`, limites de ressources CPU/mémoire.

## Lancer en local

```bash
cp .env.example .env
# éditer .env avec de vraies valeurs locales
docker compose up -d --build
# http://localhost:8088
```

## Preuve de déploiement et de résilience

Le pipeline `.github/workflows/docker-ci.yml` exécute, à chaque push, une
preuve complète et automatisée :
1. build de l'image et démarrage de l'orchestration complète (`docker compose up`)
2. attente que les deux services passent `healthy`
3. preuve fonctionnelle réelle (ajout d'une note via HTTP, vérification `/health.php`)
4. **simulation de panne** : le conteneur applicatif est tué volontairement (`docker kill`)
5. **preuve de reprise automatique** : la politique `restart: unless-stopped`
   relance le conteneur sans intervention humaine ; le pipeline attend qu'il
   redevienne `healthy` puis revérifie `/health.php`
6. nettoyage complet

Voir l'historique des runs GitHub Actions pour les logs réels de ce cycle
panne/reprise.

## Sécurité

- Aucun mot de passe en dur : `DB_PASSWORD` / `DB_ROOT_PASSWORD` fournis via
  variables d'environnement (`.env`, jamais commité — voir `.env.example`
  pour la liste des variables attendues, sans valeur réelle).
- Requêtes SQL exclusivement via PDO paramétré (pas de concaténation de
  chaînes → pas d'injection SQL possible).
- Conteneur applicatif exécuté en utilisateur non-root (`www-data`).
