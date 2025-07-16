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
