# Livret Back-End

## Description
Ce projet est une application back-end développée avec le framework Laravel. Il fournit une API pour gérer les livrets et les utilisateurs, ainsi que des fonctionnalités d'authentification et de gestion des suggestions.

## Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL ou un autre système de gestion de base de données compatible

## Installation

1. Clonez le dépôt :
    ```sh
    git clone https://github.com/Mehdirps/Livret-Back-End
    cd Livret-Back-End
    ```

2. Installez les dépendances PHP avec Composer :
    ```sh
    composer install
    ```

3. Copiez le fichier [.env.example](http://_vscodecontentref_/0) en [.env](http://_vscodecontentref_/1) et configurez vos variables d'environnement :
    ```sh
    cp .env.example .env
    ```

4. Générez la clé de l'application :
    ```sh
    php artisan key:generate
    ```

5. Configurez votre base de données dans le fichier [.env](http://_vscodecontentref_/2) :
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nom_de_votre_base_de_donnees
    DB_USERNAME=votre_nom_d_utilisateur
    DB_PASSWORD=votre_mot_de_passe
    ```

6. Exécutez les migrations de la base de données :
    ```sh
    php artisan migrate
    ```

7. Démarrez le serveur de développement :
    ```sh
    php artisan serve
    ```

## Structure du Projet

- [app](http://_vscodecontentref_/3) : Contient le code source de l'application, y compris les contrôleurs, les modèles et les fournisseurs de services.
- [bootstrap](http://_vscodecontentref_/4) : Contient les fichiers de démarrage de l'application.
- [config](http://_vscodecontentref_/5) : Contient les fichiers de configuration de l'application.
- [database](http://_vscodecontentref_/6) : Contient les migrations et les seeds de la base de données.
- [public](http://_vscodecontentref_/7) : Contient le fichier d'entrée principal (`index.php`) et les ressources publiques.
- `resources/` : Contient les vues et les ressources front-end.
- [routes](http://_vscodecontentref_/8) : Contient les fichiers de définition des routes.
- [storage](http://_vscodecontentref_/9) : Contient les logs et les fichiers générés par l'application.
- [tests](http://_vscodecontentref_/10) : Contient les tests unitaires et fonctionnels.

## Routes API

### Authentification
- `POST /api/auth/login` : Connexion d'un utilisateur.
- `POST /api/auth/register` : Inscription d'un nouvel utilisateur.

### Livrets
- `GET /api/livret/{slug}/{id}` : Affiche les détails d'un livret.
- `POST /api/dashboard/first_login` : Crée un nouveau livret lors de la première connexion.

## Tests

Pour exécuter les tests, utilisez la commande suivante :
```sh
php artisan test