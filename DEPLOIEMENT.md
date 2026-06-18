# Déploiement — Portail Support BFS

## Vue d'ensemble

```
Développement (Git)  →  GitHub SupportBFS  →  GitHub Actions (FTP)  →  public_html/support/
```

Pas de Git natif côté serveur mutualisé : le déploiement se fait par **synchronisation FTP**.

---

## 1. Secrets GitHub Actions (déjà configurés)

Dans **Settings → Secrets and variables → Actions** du repo `SupportBFS` :

| Secret | Description |
|---|---|
| `FTP_SERVER` | Hôte FTP/FTPS fourni par cPanel |
| `FTP_USERNAME` | Identifiant FTP |
| `FTP_PASSWORD` | Mot de passe FTP |

Ces valeurs ne doivent **jamais** apparaître dans le code.

---

## 2. Déploiement automatique (GitHub Actions)

Fichier : `.github/workflows/deploy.yml`

### Phase actuelle : déclenchement MANUEL

Le workflow ne se lance **pas** automatiquement sur push. Pour déployer :

1. Aller sur GitHub → **Actions** → **Deploy to cPanel (FTP)**
2. Cliquer **Run workflow** → branche `main` (ou `phase-1` selon l'état du repo)

### Activer le déploiement auto (après validation)

Décommenter dans `deploy.yml` :

```yaml
on:
  workflow_dispatch:
  push:
    branches:
      - main
```

Chaque push sur `main` déclenchera alors un sync FTP.

### Fichiers exclus du transfert FTP

| Exclusion | Raison |
|---|---|
| `.git/`, `.github/` | Métadonnées développement |
| `*.md` | Documentation interne |
| `config/config_inc.php` | Secrets production (DB, SMTP) — **jamais écrasé** |
| `tests/`, `docbook/`, `build/` | Fichiers inutiles en prod |
| `attachments/`, `project/` | Données utilisateur (pièces jointes) — **préservées** |

`dangerous-clean-slate: false` : synchronisation incrémentale, sans effacement total du dossier cible.

### Chemin serveur

```
server-dir: public_html/support/
```

Si la racine FTP de votre compte cPanel n'est pas le home directory, ajuster ce chemin (ex. `/support/` si FTP ouvre directement sur `public_html/`).

---

## 3. Premier déploiement manuel (FTP / cPanel File Manager)

### Ordre recommandé

1. **Créer la base MySQL** (cPanel → MySQL Databases)
   - Base + utilisateur avec tous les privilèges
   - Noter hôte, nom, utilisateur, mot de passe

2. **Créer la boîte email** `support@bfs.tn` (cPanel → Email Accounts)

3. **Transférer les fichiers MantisBT** vers `public_html/support/`
   - Via GitHub Actions (workflow manuel) **ou** FTP client (FileZilla, etc.)
   - Transférer tout le contenu du repo **sauf** les exclusions ci-dessus

4. **Créer `config/config_inc.php` sur le serveur**
   - Copier `config/config_inc.php.sample` → `config/config_inc.php`
   - Renseigner DB, salt, SMTP (voir modèle)

5. **Vérifier les permissions**
   - `config/` : writable pendant l'installation (755 ou 775 selon hébergeur)
   - `attachments/` : writable si upload disque (créer le dossier si absent, chmod 755)

6. **Lancer l'installation MantisBT**
   - Ouvrir `https://support.bfs.tn/admin/install.php`
   - Suivre l'assistant (création tables + compte admin)
   - **Supprimer ou renommer** `admin/install.php` après installation

7. **Installer le plugin BFS**
   - Manage → Manage Plugins → installer **BFS Support Portal**

8. **Configurer la langue**
   - `$g_default_language = 'french'` dans `config_inc.php`

### Vérifications post-déploiement

- [ ] Page login accessible sur https://support.bfs.tn
- [ ] Logo BFS visible (après activation plugin Phase 2)
- [ ] Connexion admin OK
- [ ] Test email sortant depuis MantisBT (Manage → Configuration → Email)
- [ ] Upload pièce jointe test sur un ticket
- [ ] `admin/install.php` inaccessible ou supprimé

---

## 4. Mises à jour ultérieures

1. Commit + push sur `main` (ou workflow manuel)
2. GitHub Actions synchronise les fichiers modifiés
3. `config_inc.php` et `attachments/` ne sont **pas** touchés
4. Après upgrade MantisBT majeur : vérifier compatibilité plugin `BfsPortal`

---

## 5. Rollback

En cas de problème après déploiement :

1. Restaurer une sauvegarde FTP précédente (cPanel → Backup si disponible)
2. Ou re-déployer un commit Git antérieur via Actions

La base de données n'est **pas** affectée par le déploiement FTP (sauf si scripts SQL exécutés manuellement).
