# CasPratiqueCS - Prérequis
# AWS SDK pour PHP - https://docs.aws.amazon.com/fr_fr/sdk-for-php/v3/developer-guide/welcome.html
Le SDK de aws fonctionne via des credentials présent dans votre dossier .aws sous Windows
https://docs.aws.amazon.com/cli/latest/userguide/install-cliv2.html

Le GPG pour utiliser les composants dans le code
https://blog.ghostinthemachines.com/2015/03/01/how-to-use-gpg-command-line/

# Première étape - changer les accès base de données
1 - Changer ou copier et créer les accès de la base de donnée dans le fichier .env

# Deuxième étape - Lancer les scripts de création de base de données
2 - A la racine du projet, dans le dossier create_database <br>
3 - copier et coller la première ligne pour créer la base de donnée <br>
4 - puis faite de même pour la création des tables <br>
Attention : veillez à bien sélectionner ou être à l'intérieur de votre BDD avant de lancer le script !

# Troisième étape - Téléchargement des librairies
6 - Lancer la commande "composer install" dans votre terminal <br>
Attention : télécharger composer, https://getcomposer.org/download/ si besoin !

# Quatrième étape - Lancer le server en local
7 - Lancer la commande "symfony serve" dans votre terminal


