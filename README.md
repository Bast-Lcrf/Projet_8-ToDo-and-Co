# ToDo & Co [![Codacy Badge](https://app.codacy.com/project/badge/Grade/128d57b24f414d49ae2a65c10fad75e1)](https://app.codacy.com/gh/Bast-Lcrf/Projet_8-ToDo-and-Co/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

# Info generales
* Montée de version
* Implémentation de nouvelle fonctionnalités
* Correction de quelques anomalies
* Implémentations des tests automatisés

# Codacy analyse

[Disponible ici](https://app.codacy.com/gh/Bast-Lcrf/Projet_8-ToDo-and-Co/dashboard)
* Main => dernière version du projet
* Deprecated => première version du projet

# Technologies
## Ancienne version
* [PHP:5.5.9](https://www.php.net/)
* [Symfony:3.1](https://symfony.com/doc/current/index.html)

## Nouvelle version
* [PHP:8.2.1](https://www.php.net/)
* [Symfony:6.2.6](https://symfony.com/doc/current/index.html)
* [Apache 2.0](https://www.apachelounge.com/download/VC15/)
* [MySQL 5.7.32](https://downloads.mysql.com/archives/installer/)
* [Composer](https://getcomposer.org/download/)
* Server : Pour le serveur, vous pouvez utiliser [MAMP](https://www.mamp.info/en/mac/) comme moi, ou celui de votre choix.

## Installation
Ouvrez une interface de commande et cloner le repository dans un dossier
```
git clone https://github.com/Bast-Lcrf/Projet_8-ToDo-and-Co
```
Se placer à la racine du projet et installer tous les bundles avec la commande
```
php composer.phar install
```
Faire une copie de votre fichier ```.env``` que vous renommez en ```.env.local``` et modifier la partie ```DATABASE_URL``` avec vos informations de base de données (nom d'utilisateur, mot de passe, nom de la bdd, etc)

Faire la commande suivante pour créer la base de données
```
php bin/console doctrine:schema:create
```
Une fois ces étapes réalisées, lancer MAMPServer puis faite  ```symfony server:start -d``` en ligne de commande a la racine du projet.

Page d'acceuil: ```http://127.0.0.1:8000/```

