# EcoRide

Plateforme web de covoiturage écologique
EcoRide est une application de covoiturage éco-responsable qui permet aux passagers de partager des trajets en voiture tout en gagnant des crédits, et aux conducteurs de monétiser leurs places disponibles.

## 1. Contexte & réflexion sur la stack

Le projet EcoRide vise à proposer une plateforme web de covoiturage écologique, simple d’utilisation et sécurisée.  
Le choix des technologies s’appuie sur leur robustesse, leur documentation et mon expérience personnelle :

- **Front-end :** HTML5, CSS3 (Bootstrap pour le responsive), JavaScript  
  _Justification : Universel, compatible mobile/desktop, facile à prendre en main._
- **Back-end :** PHP avec le framework Symfony  
  _Justification : Symfony est un framework moderne, sécurisé, largement utilisé pour les applications web professionnelles. Je le maîtrise et il facilite la gestion de projets structurés, la sécurité, les tests, la modularité et l’intégration de bases de données multiples (MySQL & MongoDB)._
- **Base de données relationnelle :** MySQL  
  _Justification : MySQL est fiable, performant et bien intégré avec Symfony._
- **Base de données NoSQL :** MongoDB  
  _Justification : MongoDB offre de la flexibilité pour stocker des statistiques et des logs d’événements, ce qui complète parfaitement la partie relationnelle._
- **Outils de développement :** VS Code, Git, Trello, Docker, phpMyAdmin, MongoDB Compass  
  _Justification : Ces outils améliorent la productivité, la collaboration et la gestion de projet._

Ce choix technique garantit un développement rapide, une grande sécurité et une facilité de déploiement sur des plateformes cloud comme Heroku, Fly.io ou Vercel.

## 2. Configuration de l’environnement de développement local

Pour garantir un environnement reproductible, sécurisé et proche de la production, j’utilise les outils suivants :

- **Système d’exploitation :** MacOS
- **Éditeur de code :** Visual Studio Code
- **Gestion de versions :** Git (GitHub)
- **Gestion de projet :** Trello
- **Stack technique :**
  - **PHP 8.2** (géré avec Docker pour garantir la portabilité)
  - **Symfony 6.4** (via Composer)
  - **MySQL 8** (accès via Docker, administration via phpMyAdmin)
  - **MongoDB 6** (via Docker, administration via MongoDB Compass)
- **Outils complémentaires :**
  - Docker Desktop (pour la gestion des containers)
  - Composer (gestion des dépendances PHP)
  - phpMyAdmin (admin MySQL)
  - MongoDB Compass (admin MongoDB)
- **Commandes de base :**
  - `docker-compose up` : pour démarrer tous les services
  - `composer install` : pour installer les dépendances PHP
  - `symfony server:start` : serveur local de dev

## 3. Charte graphique EcoRide

### Palette de couleurs

- **Vert mousse (primaire)** : #4A7C59 — Utilisé pour les en-têtes, boutons principaux, icônes écolo
- **Bleu gris clair (secondaire)** : #A3BFCB — Utilisé pour les encadrés, fonds de section secondaire
- **Beige clair (fond)** : #F2EDE4 — Couleur de fond principale
- **Ocre (accent/boutons)** : #D9A441 — Boutons d’action, accents, pictos
- **Blanc cassé (texte sur fond sombre)** : #FAFAF7
- **Gris foncé (texte sur fond clair)** : #333333

### Polices

- **Titres :** Montserrat, sans-serif
- **Textes courants :** Open Sans, sans-serif

Voir le PDF du projet pour la version complète avec exemples visuels.

## 4. Wireframes et Maquettes graphiques (mockups)

Toutes les maquettes fonctionnelles (wireframes) et es maquettes graphiques finales (mockups) de l’application EcoRide, en version mobile et desktop, sont présentées dans la documentation du projet à rendre (PDF joint).

- **Wireframes mobiles et desktop :** Accueil, Inscription, Espace Admin
- **Mockups mobile et desktop :** Accueil, Inscription, Espace Admin

Les wireframes servent de base à la conception des interfaces graphiques et les mockups illustre le rendu visuel final attendu pour chaque page principale, dans le respect de la charte graphique définie plus haut.

## 5. Diagrammes & Modélisation

- **Diagramme de cas d’usage** : présente les principales fonctionnalités et parcours utilisateur selon chaque rôle (visiteur, utilisateur, employé, admin).
- **Diagramme de séquence** : illustre un exemple de flux utilisateur (ex : recherche de covoiturage).
- **Modèle conceptuel de données (MCD)** : structure de la base relationnelle (tables, relations, types de données).

Les diagrammes détaillés sont disponibles dans le PDF principal à rendre.

## 6. Initialisation du projet Symfony

Le projet EcoRide est construit sur le framework Symfony 6.4 (LTS) afin de garantir robustesse, sécurité et évolutivité.  
La structure Symfony a été initialisée à la racine du dépôt, en respectant l’organisation standard (`/src`, `/config`, `/public`, etc.), ce qui facilite la gestion des dépendances, la modularité, et l’intégration des outils nécessaires (Doctrine ORM pour MySQL, Doctrine MongoDB ODM pour MongoDB, etc.).

Les principales étapes ont été :

- Installation du squelette Symfony avec Composer (`composer create-project symfony/skeleton:"6.4.*" ./`)
- Ajout de la structure Symfony à la racine du projet existant (pour garder l’historique Git et les docs)
- Préparation à la configuration des bases de données relationnelle et NoSQL

> Voir la section “Configuration des bases de données” ci-dessous pour les paramètres de connexion.

## Astuce de configuration MongoDB (Doctrine ODM)

Pour garantir que toutes les collections MongoDB du projet soient créées dans la bonne base, il est indispensable de vérifier (et forcer si besoin) le paramètre :

- Fichier : `/config/packages/doctrine_mongodb.yaml`
- Ligne :
  ```yaml
  default_database: ecoride_db
  ```

### Validation de l’insertion NoSQL (MongoDB)

Un script de commande Symfony (`src/Command/InsertStatistiqueCommand.php`) permet d’insérer un document de test dans la collection `statistique` de la base MongoDB `ecoride_db`.

- Données insérées : nom, valeur, date de création
- La réussite de cette opération valide la bonne configuration de l’intégration NoSQL dans le projet

### Listing des Statistiques MongoDB

Une commande Symfony permet de lister toutes les statistiques présentes dans la base MongoDB (`ecoride_db`), prouvant la bonne lecture des données NoSQL depuis le projet :

```bash
php bin/console stats:list

```

### Suppression de Statistiques MongoDB

Une commande Symfony permet de supprimer une statistique spécifique de la base MongoDB en fonction de son nom :

```bash
php bin/console stats:delete "Nom de la stat"
```

## API Utilisateur (CRUD)

L’API REST de gestion des utilisateurs propose les endpoints suivants :

| Méthode | Route                         | Description                      |
| ------- | ----------------------------- | -------------------------------- |
| POST    | `/api/utilisateurs`           | Inscription                      |
| POST    | `/api/utilisateurs/connexion` | Connexion                        |
| GET     | `/api/utilisateurs/profil`    | Voir le profil (connecté)        |
| PUT     | `/api/utilisateurs/profil`    | Modifier son profil              |
| DELETE  | `/api/utilisateurs/{id}`      | Supprimer un utilisateur (admin) |

- L’inscription et la connexion génèrent un API Token unique pour chaque utilisateur.
- La sécurité est gérée par rôle (`ROLE_USER`, `ROLE_ADMIN`) et par token.
- Les mots de passe sont hashés et jamais exposés dans l’API.
- Toutes les routes sont testables via Postman ou curl (voir doc technique pour exemples de payloads).

### Exemples d’utilisation (extrait Postman) :

- Inscription :
  ```json
  POST /api/utilisateurs
  {
    "email": "demo@ecoride.fr",
    "password": "motdepasse",
    "nom": "Durand",
    "prenom": "Amélie"
  }
  ```
- Connexion :
  ```json
  POST /api/utilisateurs/connexion
  {
    "email": "demo@ecoride.fr",
    "password": "motdepasse"
  }
  ```
- Modification :
  ```json
  PUT /api/utilisateurs/profil
  {
    "ville": "Toulon"
  }
  ```

## API Covoiturage (CRUD)

L’API REST de gestion des trajets propose les endpoints suivants :

| Méthode | Route                    | Description                                       |
| ------- | ------------------------ | ------------------------------------------------- |
| GET     | `/api/covoiturages`      | Liste et recherche de covoiturages (avec filtres) |
| POST    | `/api/covoiturages`      | Création d’un trajet (chauffeur connecté)         |
| GET     | `/api/covoiturages/{id}` | Détail d’un trajet                                |
| PUT     | `/api/covoiturages/{id}` | Modification (chauffeur connecté)                 |
| DELETE  | `/api/covoiturages/{id}` | Suppression (chauffeur ou admin)                  |

### Recherche et filtres

- La route GET `/api/covoiturages` accepte les filtres suivants en paramètres d’URL :
  - `depart` : ville de départ
  - `arrivee` : ville d’arrivée
  - `date` : date du trajet au format `JJ/MM/AAAA` (ex : `20/07/2025`)

### Format des dates attendu

- Pour créer ou rechercher un trajet, **les dates doivent être envoyées au format français `JJ/MM/AAAA`**.
- Exemple JSON à envoyer pour la création :
  ```json
  {
    "villeDepart": "Toulon",
    "villeArrivee": "Nice",
    "date": "21/07/2025",
    "heureDepart": "14:00",
    "heureArrivee": "16:00",
    "voiture": 3
  }
  ```

### Sécurité

- Seul l’utilisateur connecté peut créer/modifier ses propres trajets.
- Seul le chauffeur ou un admin peut supprimer un trajet.
- Les suppressions déclenchent une notification à tous les passagers du trajet annulé.

### Groupes de serialization

- L’API expose uniquement les champs utiles via les groupes `covoiturage:read` et `covoiturage:write` (pas de données sensibles).

### Exemples de requêtes Postman

- **Création de trajet (POST)**

  ```json
  POST /api/covoiturages
  {
    "villeDepart": "Toulon",
    "villeArrivee": "Nice",
    "date": "21/07/2025",
    "heureDepart": "14:00",
    "heureArrivee": "16:00",
    "voiture": 1
  }
  ```

- **Recherche de trajets**
  ```
  GET /api/covoiturages?depart=Toulon&arrivee=Nice&date=21/07/2025
  ```

## API Participation (Réservation & gestion des places)

| Méthode | Route                                | Description                                |
| ------- | ------------------------------------ | ------------------------------------------ |
| POST    | `/api/covoiturages/{id}/participer`  | Réserver une place sur un trajet           |
| DELETE  | `/api/participations/{id}`           | Annuler une participation                  |
| POST    | `/api/participations/{id}/confirmer` | Confirmer un trajet, créditer le chauffeur |
| GET     | `/api/participations`                | Lister les participations de l’utilisateur |

- **Sécurité** : seules les actions autorisées sont possibles selon le rôle et le contexte métier.
- **Notifications** envoyées automatiquement lors des actions majeures.
- **Crédit/débit** des utilisateurs géré en temps réel.
