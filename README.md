# Jeedom Core Patch for Command Selection

Patch du Core Jeedom destiné à améliorer la sélection des commandes dans la modale de sélection des commandes Jeedom, pour les commandes **info** et **action**.

> ⚠️ **Attention :** ce projet modifie directement des fichiers du Core Jeedom.
> Un mécanisme de sauvegarde, de vérification et de rollback est intégré afin de limiter les risques lors de l'installation et de permettre une restauration du Core.

---

## Ancienne modale de sélection de commande

<img width="824" height="291" alt="image" src="https://github.com/user-attachments/assets/e3b2691d-4cdf-4509-91f9-e2318ab96a00" />

## Nouvelle version proposée par ce patch

### Recherche par arborescence objet / équipement / commande

<img width="829" height="534" alt="image" src="https://github.com/user-attachments/assets/174eb31e-249e-48a5-a770-b7a6b8db2d5e" />

### Recherche par libellé d'objet, d'équipement ou de commande

<img width="824" height="530" alt="image" src="https://github.com/user-attachments/assets/a4bdc9b6-feb6-494d-bce9-fb30f8cc9f30" />

### Recherche par ID de commande

<img width="820" height="525" alt="image" src="https://github.com/user-attachments/assets/1ab327c5-b1f1-4d46-bf93-36be3a7a6dfe" />

---

# Fonctionnalités

Le patch améliore la modale de sélection des commandes de Jeedom.

Il apporte notamment :

* amélioration de la recherche de commandes ;
* recherche par arborescence **objet / équipement / commande** ;
* recherche par objet ;
* recherche par équipement ;
* recherche par commande ;
* recherche par ID de commande ;
* amélioration de la sélection des commandes ;
* prise en charge des commandes **info** et **action** ;

---

# Fichiers concernés

Le patch concerne quatre fichiers du Core Jeedom :

| Fichier                                | Statut       | Type de modification                                 |
| -------------------------------------- | ------------ | ---------------------------------------------------- |
| `/desktop/modal/cmd.human.insert.php`  | **Remplacé** | Fichier Core remplacé par la version patchée         |
| `/core/ajax/cmd.human.insert.ajax.php` | **Ajouté**   | Nouveau fichier AJAX utilisé par le patch            |
| `/core/ajax/object.ajax.php`           | **Modifié**  | Ajout ciblé de l'action `getUISelectListDetails`     |
| `/core/class/jeeObject.class.php`      | **Modifié**  | Ajout ciblé de la méthode `getUISelectListDetails()` |

Les fichiers `object.ajax.php` et `jeeObject.class.php` ne sont pas remplacés intégralement.

Le script d'installation recherche une **ancre précise** dans chacun de ces fichiers puis insère le code nécessaire.

Cette approche permet de conserver le reste du contenu du Core Jeedom.

---

# Organisation du dépôt

Les scripts permettant d'installer et de désinstaller le patch sont regroupés dans :

```text
/corePatchInstallation/
├── commandSelectionPatchInstall.php
└── commandSelectionPatchUninstall.php
```

Les deux scripts sont indépendants.

Ils sont conçus pour être copiés directement dans des blocs **Code** de scénarios Jeedom.

---

# Installation

## Script d'installation

Le fichier :

```text
/corePatchInstallation/commandSelectionPatchInstall.php
```

contient l'intégralité du code nécessaire à l'installation du patch.

Il est destiné à être copié dans un **bloc Code** d'un scénario Jeedom.

Le script utilise directement la branche `beta` du dépôt GitHub pour télécharger les fichiers nécessaires.

Aucune modification du script n'est nécessaire avant son exécution.

---

## Procédure

Créer un scénario Jeedom dédié à l'installation.

Ajouter un bloc :

**Code**

Copier ensuite dans ce bloc le contenu de :

```text
corePatchInstallation/commandSelectionPatchInstall.php
```

Puis exécuter le scénario.

---

# Déroulement de l'installation

Le script effectue automatiquement les opérations suivantes :

```text
                    Démarrage
                        │
                        ▼
              Téléchargement des fichiers
                        │
                        ▼
              Validation syntaxe PHP
                        │
                        ▼
             Vérification du Core
                        │
                        ▼
              Création des backups
                        │
             ┌──────────┴──────────┐
             ▼                     ▼
       Remplacement             Ajout /
       cmd.human.insert.php      mise à jour
                                cmd.human.insert.ajax.php
             │                     │
             └──────────┬──────────┘
                        ▼
                Patches ciblés
                        │
             ┌──────────┴──────────┐
             ▼                     ▼
       object.ajax.php      jeeObject.class.php
             │                     │
             └──────────┬──────────┘
                        ▼
                 Validation finale
                        │
                        ▼
              Nettoyage temporaire
                        │
                        ▼
                 Installation OK
```

---

# Logs du scénario

Les opérations sont journalisées directement dans le log du scénario Jeedom.

Exemple :

```text
========================================
Installation du patch Core beta
========================================
[info] Téléchargement de cmd.human.insert.php
[info] cmd.human.insert.php téléchargé et valide
[info] Téléchargement de cmd.human.insert.ajax.php
[info] cmd.human.insert.ajax.php téléchargé et valide
[info] Backup créé : cmd.human.insert.php
[info] cmd.human.insert.php remplacé
[info] cmd.human.insert.ajax.php installé
[info] Backup créé : object.ajax.php
[info] Patch appliqué : object.ajax.php
[info] Backup créé : jeeObject.class.php
[info] Patch appliqué : jeeObject.class.php
========================================
Installation du patch Core beta terminée
========================================
```

---

# Désinstallation

## Script de désinstallation

Le fichier :

```text
/corePatchInstallation/commandSelectionPatchUninstall.php
```

contient directement l'intégralité du code nécessaire à la désinstallation.

Il est destiné à être copié dans un **bloc Code** d'un scénario Jeedom.

Aucune modification du script n'est nécessaire.

---

# Procédure de désinstallation

Créer un second scénario Jeedom dédié à la désinstallation.

Ajouter un bloc :

**Code**

Copier ensuite dans ce bloc le contenu de :

```text
corePatchInstallation/commandSelectionPatchUninstall.php
```

Puis exécuter le scénario.

---

# Déroulement de la désinstallation

La désinstallation traite séparément :

* les fichiers Core modifiés ;
* le fichier Core ajouté.

Le processus est :

```text
                 Démarrage
                     │
                     ▼
             Recherche des backups
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
 cmd.human      object.ajax   jeeObject
 .insert.php    .php          .class.php
        │            │            │
        └────────────┼────────────┘
                     ▼
             Restauration Core
                     │
                     ▼
            Suppression backups
                     │
                     ▼
       Suppression cmd.human.insert.ajax.php
                     │
                     ▼
              Désinstallation OK
```

---

# Mise à jour de Jeedom

Le patch modifie directement le Core Jeedom.

Une mise à jour de Jeedom peut donc remplacer ou modifier les fichiers concernés.

Les fichiers suivants sont notamment susceptibles d'être remplacés lors d'une mise à jour :

```text
/desktop/modal/cmd.human.insert.php
/core/ajax/object.ajax.php
/core/class/jeeObject.class.php
```

Le fichier ajouté :

```text
/core/ajax/cmd.human.insert.ajax.php
```

peut également être supprimé ou remplacé par une future évolution du Core Jeedom.

Après une mise à jour de Jeedom, il est donc recommandé de vérifier la compatibilité du patch.

Si nécessaire, réexécuter le scénario d'installation avec la version actuelle de :

```text
corePatchInstallation/commandSelectionPatchInstall.php
```

Le script vérifiera alors l'état des fichiers et réappliquera les modifications nécessaires.

---

# Sécurité

Ce projet modifie directement le système de fichiers de Jeedom.

Avant toute modification du Core, il est fortement recommandé de disposer d'une sauvegarde fonctionnelle de Jeedom.

Le mécanisme d'installation apporte plusieurs protections :

* téléchargement dans des fichiers temporaires ;
* validation de la syntaxe PHP avant installation ;
* vérification des fichiers existants ;
* sauvegarde des fichiers Core avant modification ;
* conservation du premier backup ;
* détection des patchs déjà présents ;
* installation idempotente ;
* validation PHP après modification ;
* rollback automatique en cas d'erreur pendant l'installation ;
* suppression des fichiers créés en cas de rollback ;
* nettoyage des fichiers temporaires.

Malgré ces protections, ce projet reste une modification directe du Core Jeedom.

Il ne remplace pas une sauvegarde complète de l'installation.

---

# Avertissement

> ⚠️ **Ce projet n'est pas un plugin Jeedom.**

Il modifie directement des fichiers du Core Jeedom.

Il peut donc être affecté par les mises à jour du Core et peut devenir incompatible avec une future version de Jeedom.

Il est recommandé de :

* tester le patch avant tout déploiement en production ;
* conserver une sauvegarde fonctionnelle de Jeedom ;
* vérifier la compatibilité après chaque mise à jour du Core ;
* consulter les logs du scénario après installation ;
* vérifier les fichiers restaurés après désinstallation.

---

# Licence

Ce projet contient des modifications destinées au Core Jeedom.

Le code original provenant de Jeedom reste soumis à sa licence d'origine.

Les fichiers de ce dépôt doivent être utilisés conformément aux conditions de licence applicables au projet Jeedom.
