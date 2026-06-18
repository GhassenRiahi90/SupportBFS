# Onboarding client — Portail Support BFS

> Architecture v2 (MantisBT 2.28.3) — **1 projet = 1 client**, solutions via **catégories globales**.

## Principe de confidentialité

| Niveau MantisBT | Rôle BFS | Exemple |
|---|---|---|
| **Projet** | Client | Carrefour Tunisie, Client Démo |
| **Catégorie globale** | Solution + type de demande | Sage XRT (Cash Management) — Connecteurs bancaires |

MantisBT isole la visibilité des tickets **au niveau du projet**. Deux clients ne doivent **jamais** partager le même projet.

Les solutions (Sage XRT, Sage SXA, Treasury Analytics, Support Général) sont des **catégories globales** réutilisables dans tous les projets clients (`Hériter des catégories globales` = activé).

---

## Migration automatique (plugin v0.4.0+)

Lors de l’upgrade du greffon **BFS Support Portal → 0.4.0** :

1. Crée les catégories globales `{Solution} — {Type}` (23 entrées)
2. Supprime les 4 anciens projets « solution » (s’ils sont vides) ou les **désactive** s’ils contiennent des tickets
3. Crée le projet privé **Client Démo**
4. Crée l’utilisateur test **demo.client** (reporter, accès limité à Client Démo)
5. Applique la config confidentialité (projets/tickets privés par défaut)

---

## Procédure — Nouveau client BFS

### 1. Créer le projet client

**Gérer → Clients → Créer un nouveau client**

| Champ | Valeur |
|---|---|
| Nom | Nom du client (ex. `Carrefour Tunisie`) |
| Visibilité | **Privée** |
| Hériter des catégories globales | **Coché** |
| Activé | Oui |

> Les nouveaux projets respectent `$g_default_project_view_status = VS_PRIVATE` si configuré dans `config_inc.php`.

### 2. Lier les champs personnalisés

Champs requis sur « Nouvelle demande » : `client_company`, `contract_reference`, `environment`, `business_urgency`.

**Gérer → Champs personnalisés → [chaque champ] → Projets liés** → ajouter le nouveau client.

*(Création automatique via `BfsClient::create_client_project()`.)*

### 3. Créer / inviter les utilisateurs client

**Gérer → Utilisateurs → Créer un utilisateur**

| Champ | Valeur recommandée |
|---|---|
| Niveau d’accès global | **Rapporteur** |
| Email | contact client |

Puis **Gérer → Utilisateurs → [utilisateur] → Clients** : assigner **uniquement** le projet du client (Rapporteur), projet par défaut = ce client.

### 4. Accès équipe interne BFS

| Profil | Niveau global | Accès |
|---|---|---|
| Support / consultants | **Developer** ou **Manager** | Tous projets privés |
| Administrateur | Administrator | Complet |

`$g_private_project_threshold = DEVELOPER` permet à l’équipe BFS de voir tous les projets privés sans assignment manuel.

### 5. Checklist avant production

- [ ] Projet **privé**, héritage catégories globales
- [ ] Utilisateur client limité à **un seul** projet
- [ ] Test : le client ne voit aucun ticket d’un autre projet
- [ ] Équipe BFS voit le projet

---

## Validation — Client Démo

| Élément | Valeur |
|---|---|
| Projet | **Client Démo** (privé) |
| Utilisateur | `demo.client` |
| Mot de passe initial | `Bfs-Demo-Client-2026!` |
| Email | demo.client@support.bfs.tn |

**Test :** connectez `demo.client` — seuls les tickets de Client Démo doivent être visibles. Changez le mot de passe après validation.

---

## Configuration serveur (`config_inc.php`)

```php
$g_default_project_view_status = VS_PRIVATE;
$g_default_bug_view_status     = VS_PRIVATE;
$g_private_project_threshold   = DEVELOPER;
```

---

## Référence technique

```php
require_once( 'plugins/BfsPortal/inc/BfsClient.php' );
$t_id = BfsClient::create_client_project( 'Carrefour Tunisie', 'Contrat 2026' );
BfsClient::assign_client_user( $t_id, $t_user_id, REPORTER );
```
