# Jeedom Core Patch for Command Selection

Patch du Core Jeedom destiné à améliorer la sélection des commandes dans la modale de sélection des commandes Jeedom (infos et actions).

> ⚠️ Ce projet modifie directement des fichiers du Core Jeedom.
> Un mécanisme de sauvegarde, de vérification et de rollback est intégré afin de permettre un retour à l'état original.

---

Ancienne modale de sélection de commande
<img width="824" height="291" alt="image" src="https://github.com/user-attachments/assets/e3b2691d-4cdf-4509-91f9-e2318ab96a00" />

## Nouvelle version proposée par ce patch

Recherche par arborescence objet / équipement / commande
<img width="829" height="534" alt="image" src="https://github.com/user-attachments/assets/174eb31e-249e-48a5-a770-b7a6b8db2d5e" />

Recherche par libellé d'objet, d'équipement ou de commande
<img width="824" height="530" alt="image" src="https://github.com/user-attachments/assets/a4bdc9b6-feb6-494d-bce9-fb30f8cc9f30" />

Recherche par id de commande
<img width="820" height="525" alt="image" src="https://github.com/user-attachments/assets/1ab327c5-b1f1-4d46-bf93-36be3a7a6dfe" />

## Fonctionnalités

* Modification de la modale de sélection des commandes Jeedom (info et action).
* Recherche et sélection améliorées.

## Installation

* Installation et désinstallation automatisées.
* Scripts d'installation et de désinstallation indépendants.
* Utilisation directe depuis des scénarios Jeedom.
* Téléchargement automatique des fichiers depuis GitHub.
* Vérification de la syntaxe PHP avant installation.
* Backup automatique du fichier Core original.
* Détection des fichiers déjà installés.
* Installation idempotente.
* Désinstallation avec restauration du Core.
* Rollback automatique en cas d'erreur.
* Journalisation directement dans les logs du scénario Jeedom.
* Gestion automatique des permissions `www-data:www-data` en `0644`.

---

# Fichiers concernés

Le patch concerne deux fichiers du Core Jeedom :

| Fichier                                | Statut      | Description                                         |
| -------------------------------------- | ----------- | --------------------------------------------------- |
| `/desktop/modal/cmd.human.insert.php`  | **Modifié** | Fichier Core Jeedom remplacé par la version patchée |
| `/core/ajax/cmd.human.insert.ajax.php` | **Ajouté**  | Nouveau fichier AJAX utilisé par le patch           |

### Backup

Avant le remplacement du fichier Core, une copie de l'original est créée :

```text
/var/www/html/desktop/modal/cmd.human.insert.php.corepatch-backup
```

Le backup existant n'est jamais écrasé.

Cela garantit qu'une nouvelle installation du patch ne remplace pas le fichier original sauvegardé lors de la première installation.

---

# Installation par scénario Jeedom

Pour faciliter la migration et éviter d'avoir à modifier manuellement une variable `$action`, le dépôt fournit directement **deux scripts indépendants**.

Chaque script correspond à un scénario Jeedom.

## Scripts disponibles

```text
/corePatchInstallation/
├── commandSelectionPatchInstall.php
└── commandSelectionPatchUninstall.php
```

### Installation

Le fichier :

```text
/corePatchInstallation/commandSelectionPatchInstall.php
```

contient directement le code nécessaire à l'installation du patch.

Il est destiné à être copié dans un **bloc Code** d'un scénario Jeedom dédié à l'installation.

### Désinstallation

Le fichier :

```text
/corePatchInstallation/commandSelectionPatchUninstall.php
```

contient directement le code nécessaire à la désinstallation du patch.

Il est destiné à être copié dans un **bloc Code** d'un scénario Jeedom dédié à la désinstallation.

Cette séparation permet notamment de préparer facilement des scénarios de migration sans avoir à modifier le code avant chaque utilisation.

---

# Scénario d'installation

Créer un scénario Jeedom, puis ajouter un bloc :

**Code**

Copier dans ce bloc le contenu de :

```text
corePatchInstallation/commandSelectionPatchInstall.php
```

Puis exécuter le scénario.

Le script effectue automatiquement les opérations suivantes :

```text
Téléchargement des fichiers
          ↓
Validation de la syntaxe PHP
          ↓
Vérification du fichier Core
          ↓
Création du backup
          ↓
Remplacement du fichier Core
          ↓
Installation du fichier AJAX
          ↓
Validation des fichiers installés
          ↓
Nettoyage des fichiers temporaires
          ↓
Installation terminée
```

Aucune modification du script n'est nécessaire.

---

# Scénario de désinstallation

Créer un second scénario Jeedom, puis ajouter un bloc :

**Code**

Copier dans ce bloc le contenu de :

```text
corePatchInstallation/commandSelectionPatchUninstall.php
```

Puis exécuter le scénario.

Le script effectue automatiquement :

```text
Recherche du backup
        ↓
Restauration du fichier Core
        ↓
Validation PHP
        ↓
Suppression du backup
        ↓
Suppression du fichier AJAX
        ↓
Désinstallation terminée
```

Aucune modification du script n'est nécessaire.

---

# Gestion du backup

Lors de la première installation, le fichier original :

```text
/var/www/html/desktop/modal/cmd.human.insert.php
```

est sauvegardé sous :

```text
/var/www/html/desktop/modal/cmd.human.insert.php.corepatch-backup
```

Le backup existant n'est jamais écrasé.

Cela garantit que le fichier conservé reste bien le fichier original présent avant la première installation du patch.

---

# Rollback automatique

L'installation est protégée par un mécanisme de rollback.

Si une erreur survient pendant l'installation :

* le fichier Core est restauré uniquement s'il a été modifié par cette exécution ;
* le fichier AJAX est supprimé uniquement s'il a été créé ou modifié par cette exécution ;
* les fichiers temporaires sont supprimés ;
* l'erreur est enregistrée dans le log du scénario ;
* l'exception est renvoyée.

Le script évite ainsi de laisser une installation partiellement effectuée.

---

# Désinstallation

Le script de désinstallation utilise le backup créé lors de l'installation.

Le fichier :

```text
/desktop/modal/cmd.human.insert.php
```

est restauré à partir de :

```text
/desktop/modal/cmd.human.insert.php.corepatch-backup
```

Le backup est supprimé **uniquement après une restauration réussie**.

Le fichier :

```text
/core/ajax/cmd.human.insert.ajax.php
```

est ensuite supprimé.

Les deux opérations restent indépendantes :

* l'absence du backup n'empêche pas la vérification/suppression du fichier AJAX ;
* l'absence du fichier AJAX n'empêche pas la restauration du fichier Core.

---

# Vérification de la syntaxe PHP

Les fichiers téléchargés sont vérifiés avant installation avec :

```bash
php -l <fichier>
```

Une nouvelle vérification est effectuée après installation.

Une erreur de syntaxe provoque l'arrêt de l'opération et, lorsque cela est nécessaire, le déclenchement du rollback.

---

# Logs du scénario

Exemple :

```text
[info] Installation du patch Core
[info] Téléchargement de cmd.human.insert.php
[info] cmd.human.insert.php téléchargé et valide
[info] Création du backup du fichier Core original
[info] Backup créé
[info] Remplacement de cmd.human.insert.php
[info] cmd.human.insert.php remplacé avec succès
[info] Création de cmd.human.insert.ajax.php
[info] cmd.human.insert.ajax.php installé avec succès
[info] Patch Core installé avec succès
```

En cas d'erreur :

```text
[error] Erreur : ...
[error] Rollback automatique en cours
[info] cmd.human.insert.php restauré
[info] cmd.human.insert.ajax.php supprimé
[error] Rollback terminé
```

---

# Mise à jour de Jeedom

Le patch modifie directement le Core Jeedom.

Une mise à jour de Jeedom peut donc remplacer :

```text
/desktop/modal/cmd.human.insert.php
```

et supprimer les modifications apportées par le patch.

Le fichier AJAX ajouté peut également être supprimé ou remplacé par une future version du Core.

Après une mise à jour du Core, il est donc recommandé de vérifier la compatibilité du patch.

Si nécessaire, réexécuter le scénario d'installation avec la dernière version du script :

```text
corePatchInstallation/commandSelectionPatchInstall.php
```

---

# Retour à l'état original

Pour supprimer complètement le patch, exécuter le scénario contenant :

```text
commandSelectionPatchUninstall.php
```

Après exécution, vérifier dans le log du scénario que :

* le fichier Core a été restauré ;
* le fichier AJAX a été supprimé ;
* le backup a été supprimé après restauration réussie ;
* aucune erreur de rollback n'est apparue.

L'état attendu est :

```text
/var/www/html/desktop/modal/cmd.human.insert.php
    → fichier Core original

/var/www/html/core/ajax/cmd.human.insert.ajax.php
    → absent

/var/www/html/desktop/modal/cmd.human.insert.php.corepatch-backup
    → absent
```

---

# Sécurité

Ce projet modifie directement le système de fichiers de Jeedom.

Avant toute modification du Core, il est recommandé de disposer d'une sauvegarde fonctionnelle de Jeedom.

Le mécanisme d'installation apporte plusieurs protections :

* téléchargement vers des fichiers temporaires ;
* validation PHP avant installation ;
* backup du fichier Core original ;
* conservation du backup existant ;
* vérification après installation ;
* rollback automatique en cas d'erreur ;
* nettoyage des fichiers temporaires ;
* restauration conditionnelle des fichiers réellement modifiés.

Malgré ces protections, ce projet reste une modification directe du Core Jeedom.

---

# Avertissement

Ce projet n'est pas un plugin Jeedom.

Il modifie directement des fichiers du Core et peut donc être affecté par les mises à jour de Jeedom.

Il est recommandé de tester le patch avant tout déploiement sur une installation de production et de conserver une sauvegarde de Jeedom.

---

# Licence

Ce projet contient des modifications destinées au Core Jeedom.

Le code original provenant de Jeedom reste soumis à sa licence d'origine.

Les fichiers de ce dépôt doivent être utilisés conformément aux conditions de licence applicables au projet Jeedom.
