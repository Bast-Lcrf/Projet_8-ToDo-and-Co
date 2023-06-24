# Guide du contribution

# Bien débuter
Bienvenue dans le projet ! Pour commencer, vous devrez installer les dépendances du projet et configurer votre environnement local.

Les instructions d'installation et de configuration se trouvent dans le fichier README.md à la racine du projet. Veuillez suivre attentivement ces instructions pour vous assurer que le projet est correctement configuré et que toutes les dépendances sont installées.

Une fois que vous avez terminé le processus d'installation et de configuration, vous devriez être prêt à commencer à contribuer au projet.

# Noms des branches
* Catégorie
Une branche git doit commencer par une catégorie. Choisissez-en un : feature, bugfix, hotfix, ou test.
* Référence
Après la catégorie, il devrait y avoir un "/" suivi de la référence de l'issue/ticket sur lequel vous travaillez. S'il n'y a pas de référence, ajoutez simplement "no-ref".
* Description
Après la référence, il devrait y avoir un autre "/" suivi d'une description qui résume le but de cette branche spécifique. Cette description doit être courte.
Par défaut, vous pouvez utiliser le titre du problème/ticket sur lequel vous travaillez. Remplacez simplement n'importe quel caractère spécial par "-".

Pour résumer, suivez ce modèle lors de la création de branches: 
```
git branch <catégorie/référence/description>
```

# Noms des commits :
* Catégorie
Un message de validation doit commencer par une catégorie de changement. Vous pouvez utilisez les 4 catégories suivante : feat, fix, refactor et chore.

feat est pour ajouter une nouvelle fonctionnalité,
fix est pour corriger un bug,
refactor sert à modifier le code à des fins de performance ou de commodité (par exemple, la lisibilité),
chore est pour tout le reste (rédaction de documentation, formatage, ajout de tests, nettoyage de code inutile etc...).

Après la catégorie, il devrait y avoir un ":" annonçant la description du commit.

* Description
Après les deux points, une courte description doit décrire les modifications ou ajouts apportés.
Les instructions doivent être séparés entres elles par un ";".

Pour résumer, suivez ce modèle lors de vos commits :
```
git commit -m '<catégorie : faire quelque chose ; faire d'autres choses>'
```

# Tests avec PHPUnit 
Chaque controller en phase de test est indépendant, c'est a dire qu'il créé les données dont il a besoin pour ses tests et les supprimes à la fin du cycle.
Merci de respecter cette règle pour garder une base de données de test vide et propre.

Pour lancer un cycle complet, taper cette ligne de commande : 
```
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text 
```
Pour lancer un test plus précis, taper celle ci :
```
vendor/bin/phpunit --filter "controller / fonction à tester"
```