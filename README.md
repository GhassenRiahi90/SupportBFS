# Portail Support BFS — MantisBT

Portail de support client pour **Business Financial Solutions** (Sage XRT, Sage SXA, BFS Treasury Analytics), basé sur MantisBT 2.28.3.

- **Production** : https://support.bfs.tn
- **Document root cPanel** : `public_html/support`
- **Dépôt** : https://github.com/GhassenRiahi90/SupportBFS

## Structure

| Dossier / fichier | Rôle |
|---|---|
| Racine du repo | Application MantisBT (déployée telle quelle, sans build) |
| `plugins/BfsPortal/` | Customisation BFS (thème, dashboard, emails) |
| `config/config_inc.php.sample` | Modèle de configuration production |
| `config/config_inc.php` | Config prod (**non versionné**, à créer sur le serveur) |
| `.github/workflows/deploy.yml` | Déploiement FTP vers cPanel |

## Documentation

- [VERSION_INSTALLEE.md](VERSION_INSTALLEE.md) — version MantisBT et prérequis serveur
- [DEPLOIEMENT.md](DEPLOIEMENT.md) — procédure FTP manuelle et GitHub Actions
- [CUSTOMISATIONS_BFS.md](CUSTOMISATIONS_BFS.md) — inventaire des personnalisations

## Développement local

1. Cloner ce dépôt
2. Copier `config/config_inc.php.sample` → `config/config_inc.php` et renseigner DB locale
3. Accéder à `admin/install.php` pour initialiser la base

## Déploiement

Le workflow FTP est configuré en **déclenchement manuel** (`workflow_dispatch`) jusqu'à validation.
Voir [DEPLOIEMENT.md](DEPLOIEMENT.md).
