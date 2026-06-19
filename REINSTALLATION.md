# Réinstallation complète — Portail Support BFS

> Utiliser ce guide si l’installation est bloquée (ex. erreur mbstring) ou si vous repartez de zéro.

---

## Vue d’ensemble

| Étape | Où | Durée |
|-------|-----|-------|
| 1. PHP + extensions | cPanel | 5 min |
| 2. Base MySQL | cPanel / phpMyAdmin | 5 min |
| 3. Fichiers | GitHub Actions (FTP) | 5 min |
| 4. Config | cPanel File Manager | 5 min |
| 5. Pré-vol | Navigateur | 1 min |
| 6. Install MantisBT | Navigateur | 5 min |
| 7. Plugin BFS | MantisBT admin | 2 min |

---

## Étape 1 — PHP (OBLIGATOIRE avant tout)

Le site utilise **PHP-FPM** (`fpm-fcgi`). L’outil **Select PHP Version** (CloudLinux) **ne suffit pas**.

### 1a. Version PHP

**cPanel → MultiPHP Manager**

- Cocher **`support.bfs.tn`**
- Choisir **`PHP 8.3 (ea-php83)`** (ou **ea-php82** si 8.3 absent)
- **Apply**

> Éviter `alt-php82` si les extensions ne se chargent pas en web.

### 1b. Extensions

**cPanel → MultiPHP INI Editor**

- Domaine : **`support.bfs.tn`**
- Version : celle choisie à l’étape 1a
- Mode **Basic** → activer :
  - `mbstring`
  - `mysqli`
  - `gd`
  - `curl`
  - `fileinfo`
  - `zip`
- **Apply**

Attendre **2–3 minutes**, puis ouvrir :

```
https://support.bfs.tn/bfs-preflight.php
```

**Tous les points doivent être verts** avant de continuer.

---

## Étape 2 — Base MySQL

### Option A — Base dédiée (recommandé)

**cPanel → MySQL Databases**

1. Créer base : `bfstn1_mantis`
2. Créer utilisateur + mot de passe fort
3. Associer utilisateur → base (ALL PRIVILEGES)

### Option B — Réutiliser l’ancienne base

**phpMyAdmin** → exécuter `scripts/reinstall-drop-mantis-tables.sql`  
(supprime uniquement les tables `mantis_*`).

---

## Étape 3 — Fichiers sur le serveur

### 3a. Nettoyer l’ancienne install (cPanel File Manager)

Dans `public_html/support/` :

- Supprimer **`config/config_inc.php`** (sera recréé)
- Supprimer **`.htaccess`** et **`.user.ini`** s’ils existent encore
- Supprimer **`bfs-php-check.php`**
- Conserver **`attachments/`** seulement si vous voulez garder des pièces jointes

### 3b. Redéployer depuis Git

GitHub → **Actions** → **Deploy to cPanel (FTP)** → **Run workflow** → branche **`phase-1`**

---

## Étape 4 — Configuration

**File Manager** → `public_html/support/config/`

1. Copier `config_inc.php.sample` → **`config_inc.php`**
2. Renseigner :

| Variable | Valeur |
|----------|--------|
| `$g_db_*` | Base / user / mot de passe (étape 2) |
| `$g_crypto_master_salt` | Chaîne aléatoire ≥ 16 caractères (nouvelle !) |
| `$g_smtp_*` | Boîte `support@bfs.tn` |

3. Permissions : `config/` → **755** (ou 775)

---

## Étape 5 — Pré-vol

```
https://support.bfs.tn/bfs-preflight.php
```

Si un point est **KO** → retour étape 1. **Ne pas ouvrir install.php** tant que ce n’est pas vert.

---

## Étape 6 — Installation MantisBT

```
https://support.bfs.tn/admin/install.php
```

1. Type admin : **All In One**
2. Vérifier la connexion base
3. Créer le compte **administrateur** BFS
4. Fin de l’assistant

**Sécurité :** renommer ou supprimer `admin/install.php` après installation.

---

## Étape 7 — Plugin BFS

1. **Gérer → Greffons** → installer **BFS Support Portal**
2. Cliquer **Mettre à jour** (schéma v0.8.0)
3. La migration architecture v2 s’exécute automatiquement (Client Démo, catégories globales)

Mot de passe utilisateur test `demo.client` : config plugin `demo_client_password` (Manage → Configuration → Plugin).

---

## Étape 8 — Nettoyage post-install

Supprimer du serveur :

- `bfs-preflight.php`
- `admin/install.php` (renommé ou supprimé)

---

## Vérifications finales

- [ ] https://support.bfs.tn → page de connexion
- [ ] Connexion admin OK
- [ ] Mon tableau de bord BFS visible
- [ ] Test `demo.client` sur Client Démo
- [ ] Email test (Manage → Configuration → Email)
- [ ] `bfs-preflight.php` supprimé

---

## En cas de blocage mbstring persistant

Ticket hébergeur :

> Bonjour, le sous-domaine support.bfs.tn (PHP 8.2/8.3 FPM, ea-php) affiche « mbstring extension is not enabled » alors que le fichier `/opt/cpanel/ea-php82/root/usr/lib64/php/modules/mbstring.so` existe. Merci d’activer mbstring pour ce vhost PHP-FPM.
