# URGENT — Erreur 500 sur support.bfs.tn

Si **bfs-preflight.php** ou **admin/install.php** affichent **500 Internal Server Error** après avoir modifié les extensions PHP :

## Cause

**MultiPHP INI Editor** (mode Editor) a souvent écrit un **`.user.ini`** avec des lignes `extension=...` invalides pour PHP-FPM → crash PHP sur chaque script.

## Correction (2 min)

### 1. Supprimer `.user.ini`

**cPanel → File Manager** → `public_html/support/`

- Afficher les fichiers cachés (Settings → Show Hidden Files)
- **Supprimer** le fichier **`.user.ini`**

### 2. Réinitialiser l’INI Editor

**MultiPHP INI Editor** → `support.bfs.tn` → PHP 8.2

- Mode **Editor** : supprimer toute ligne `extension=mbstring`, `extension=gd`, `extension=fileinfo`
- Passer en mode **Basic** : activer **mbstring**, **gd**, **fileinfo** avec les cases à cocher
- **Apply**

### 3. Attendre 3 minutes, tester

```
https://support.bfs.tn/bfs-test.php     → doit afficher "OK 8.2.x"
https://support.bfs.tn/bfs-preflight.php
```

---

## Si 500 persiste

Vérifier aussi dans `public_html/support/` :

- `.htaccess` — supprimer si présent (sauf celui fourni par WordPress ailleurs)
- `php.ini` — supprimer si présent dans ce dossier

Puis ticket hébergeur : activer mbstring/gd/fileinfo côté serveur pour **support.bfs.tn**.
