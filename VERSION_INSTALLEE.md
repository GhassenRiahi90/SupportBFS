# Version installée — Portail Support BFS

## MantisBT

| Champ | Valeur |
|---|---|
| **Version** | 2.28.3 |
| **Date de récupération** | 18 juin 2026 |
| **Release officielle** | https://github.com/mantisbt/mantisbt/releases/tag/release-2.28.3 |
| **Archive source** | `mantisbt-2.28.3.zip` (tag Git `release-2.28.3`) |

## Environnement cPanel (confirmé)

| Composant | Valeur |
|---|---|
| **Hébergement** | Mutualisé cPanel |
| **Sous-domaine** | support.bfs.tn |
| **Document root** | `public_html/support` |
| **PHP** | **8.3** (global, actif sur bfs.tn et support.bfs.tn) |
| **Extensions PHP** | ctype, filter, hash, json, session, tokenizer, mbstring, fileinfo, pdo_mysql, mysqli, gd, zip — toutes confirmées actives |

### Compatibilité

PHP 8.3 dépasse largement le minimum requis par MantisBT 2.28.3 (PHP 7.1+, recommandé 8.4+). Aucun blocage identifié.

## Base de données (à renseigner après création cPanel)

| Champ | Valeur |
|---|---|
| Hôte | `localhost` (typique cPanel) |
| Nom de la base | _à compléter_ |
| Utilisateur MySQL | _à compléter_ |
| Création | cPanel → MySQL Databases / phpMyAdmin |

## Email sortant

| Champ | Valeur |
|---|---|
| Expéditeur | support@bfs.tn |
| Nom affiché | Support BFS |
| SMTP host | mail.bfs.tn (à confirmer dans cPanel → Email Accounts → Connect Devices) |
| Port | 465 SSL ou 587 TLS (selon indication cPanel) |

## Charte graphique — palette calibrée logo BFS

| Rôle | Code |
|---|---|
| Bleu primaire foncé | `#0073B1` |
| Bleu primaire clair | `#54B9E9` |
| Gris anthracite (texte) | `#5B6770` |
| Gris clair (accents) | `#A6A9AA` |
| Noir | `#111111` |
| Blanc | `#FFFFFF` |

Les couleurs fonctionnelles statut (vert `#2E7D32`, orange `#E65100`, rouge `#B71C1C`) seront appliquées en Phase 2–3.

## Assets marque

| Fichier | Emplacement |
|---|---|
| Logo header / login | `plugins/BfsPortal/assets/img/bfs-logo.png` |
| Favicon | `plugins/BfsPortal/assets/img/favicon.ico` |

Source : logo officiel BFS (aligné sur bfs.tn/wp-content/uploads/2023/04/bfs.png).
