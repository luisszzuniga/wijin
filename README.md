# Projet Laravel

## Description

Ce projet est une application web développée avec le framework Laravel. Ce README vous guidera à travers les étapes nécessaires pour configurer et exécuter le projet localement.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- PHP >= 7.3
- Composer
- Docker et Docker Compose

## Installation

1. **Cloner le dépôt :**
   ```bash
   git clone https://github.com/votre-utilisateur/votre-projet.git
   cd votre-projet
   ```

2. **Installer les dépendances :**
    ```bash
   composer install
   ```
   
3. **Créer un lien symbolique pour le stockage :**
    ```bash
    php artisan storage:link
    ```

4. **Générer la clé de l'application :**
    ```bash
    php artisan key:generate
    ```

## Configuration de l'environnement

1. **Copier le fichier d'exemple d'environnement :**
    ```bash
    cp .env.example .env
    ```
2. **Modifier les configurations dans le fichier .env si nécessaire.**

## Utilisation de Laravel Sail

Laravel Sail est un environnement de développement léger basé sur Docker pour Laravel. Voici les commandes pour démarrer et configurer votre application avec Sail :

1. **Démarrer les conteneurs Docker :**
    ```bash
    vendor/bin/sail up
    ```
   
2. **Exécuter les migrations de la base de données :**
    ```bash
    vendor/bin/sail artisan migrate
    ```
   
3. **Peupler la base de données avec des données fictives :**
    ```bash
    vendor/bin/sail artisan db:seed
    ```
   
## Accès aux services

Une fois les conteneurs Docker démarrés, vous pouvez accéder aux services suivants :
- Application : [localhost:80](localhost:80)
- phpMyAdmin : [localhost:8080](localhost:8080)
- Mailpit : [localhost:8025](localhost:8025)

## Tests

Pour exécuter les tests unitaires et fonctionnels, utilisez la commande suivante :

```bash
vendor/bin/sail artisan test
```
