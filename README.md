# Jeedom Core Patch for Command Selection

Patch du Core Jeedom destiné à améliorer la sélection des commandes dans la modale de sélection des commandes humaines.

> ⚠️ Ce projet modifie directement des fichiers du Core Jeedom.
> Un mécanisme de sauvegarde, de vérification et de rollback est intégré afin de permettre un retour à l'état original.

---

## Fonctionnalités

* Modification de la modale de sélection des commandes humaines.
* Ajout du traitement AJAX associé.
* Recherche et sélection améliorées.
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

# Installation idempotente

Le script peut être exécuté plusieurs fois.

Avant de remplacer le fichier Core, il compare le fichier existant avec la version téléchargée.

Si les fichiers sont identiques :

```text
cmd.human.insert.php déjà installé
```

Aucun remplacement inutile n'est effectué.

Le même principe est appliqué au fichier AJAX.

Cela permet notamment de relancer le scénario d'installation sans générer de modifications inutiles.

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

# Sources des fichiers

Les fichiers installés sont présents dans le dépôt :

```text
/desktop/modal/cmd.human.insert.php
/core/ajax/cmd.human.insert.ajax.php
```

Les scripts d'installation et de désinstallation récupèrent ces fichiers directement depuis la branche `main`.

---

# Structure du dépôt

```text
.
├── README.md
├── desktop/
│   └── modal/
│       └── cmd.human.insert.php
│
├── core/
│   └── ajax/
│       └── cmd.human.insert.ajax.php
│
└── corePatchInstallation/
    ├── commandSelectionPatchInstall.php
    └── commandSelectionPatchUninstall.php
```

Les fichiers situés dans `corePatchInstallation/` sont destinés à être copiés directement dans les blocs **Code** des scénarios Jeedom.

---

# Mise à jour du patch

Pour mettre à jour le patch :

1. Mettre à jour les fichiers patchés dans le dépôt.
2. Mettre à jour les scripts d'installation et de désinstallation si nécessaire.
3. Exécuter à nouveau le scénario contenant `commandSelectionPatchInstall.php`.

Le script compare automatiquement les fichiers présents sur l'installation avec les versions disponibles dans le dépôt.

Si le fichier a changé, il est remplacé.

Le backup original existant est conservé et n'est jamais écrasé.

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

---

# Auteur

**noodom**

Patch développé pour Jeedom.
