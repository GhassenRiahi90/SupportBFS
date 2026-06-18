# Customisations BFS — Inventaire

> Dernière mise à jour : Phase 1 (18 juin 2026)

## Principe

Toute personnalisation passe par **plugins** et **fichiers config dédiés**, sans modification du core MantisBT, pour rester maintenable lors des mises à jour de sécurité.

---

## Fichiers core MantisBT modifiés

| Fichier | Modification |
|---|---|
| _Aucun_ | Core intact (release 2.28.3) |

---

## Plugin custom : `plugins/BfsPortal/`

| Fichier | Rôle | Phase |
|---|---|---|
| `BfsPortal.php` | Plugin principal (hooks layout, emails, dashboard) | 2 |
| `assets/css/bfs.css` | Charte graphique BFS complète | 2 |
| `assets/js/bfs-portal.js` | Bloc d'accueil login, classe body | 2 |
| `assets/img/bfs-logo.png` | Logo header / login / emails | 1 |
| `assets/img/favicon.ico` | Favicon navigateur | 1 |

### Phases suivantes (prévu)

| Élément | Phase |
|---|---|
| Redesign login complet | 2 |
| Dashboard accueil post-login | 4 |
| Templates emails HTML BFS | 5 |
| Notifications par projet/solution | 5 |
| Relance tickets sans réponse | 5 |

---

## Fichiers config versionnés

| Fichier | Rôle | Versionné |
|---|---|---|
| `config/config_inc.php.sample` | Modèle config BFS (DB, SMTP, URL) | Oui |
| `config/config_inc.php` | Config production (secrets) | **Non** |
| `config/custom_strings_inc.php` | Vocabulaire / branding portail | Phase 2 |
| `config/custom_constants_inc.php` | Statuts / criticité custom | Phase 3 |

---

## Workflow CI/CD

| Fichier | Rôle |
|---|---|
| `.github/workflows/deploy.yml` | Déploiement FTP → `public_html/support/` |

Workflows MantisBT upstream (`mantisbt.yml`, `documentation.yml`) conservés mais sans impact sur le déploiement BFS.

---

## Données métier (admin MantisBT / SQL)

À configurer en Phase 3 :

- 4 projets (Sage XRT, Sage SXA, Treasury Analytics, Support Général)
- Catégories par projet
- Statuts workflow BFS
- Niveaux de criticité
- Champs personnalisés (société, contrat, environnement, urgence)

---

## Upgrade MantisBT

1. Télécharger nouvelle release stable
2. Remplacer fichiers core (admin, api, core, css, js, lang, library, vendor…)
3. **Ne pas écraser** : `config/config_inc.php`, `plugins/BfsPortal/`, `attachments/`
4. Relancer `admin/install.php` si migration DB requise
5. Tester plugin `BfsPortal` et customisations
