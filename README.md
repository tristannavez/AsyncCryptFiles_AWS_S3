# AsyncCryptFiles_AWS_S3 - Introduction
Création d'une plateforme de partage de fichiers qui va chercher dans un bucket Amazon S3.
Les fichiers sont cryptés de manière asynchrone.
Une clé public est nécessaire au cryptage du fichier, et une clé privé permettra le déchiffrement.
Les fichiers sont signés numériquement, et l'utilisateur est authentifié.

# AsyncCryptFiles_AWS_S3 - Prérequis
# AWS SDK pour PHP - https://docs.aws.amazon.com/fr_fr/sdk-for-php/v3/developer-guide/welcome.html
Le SDK de aws fonctionne via des credentials présent dans votre dossier .aws sous Windows
https://docs.aws.amazon.com/cli/latest/userguide/install-cliv2.html

Le GPG pour utiliser les composants dans le code
https://blog.ghostinthemachines.com/2015/03/01/how-to-use-gpg-command-line/

# Configurer l'accès mysql
1 - Modifier le fichier .env

# Lancer les scripts de création de base de données
2 - A la racine du projet, dans le dossier create_database <br>
3 - Copier et coller la première ligne pour créer la base de donnée <br>
4 - Puis faite de même pour la création des tables <br>

# Téléchargement des librairies
5 - Lancer la commande "composer install" dans votre terminal <br>
Attention : télécharger composer, https://getcomposer.org/download/ si besoin !

# Quatrième étape - Lancer le server en local
6 - Lancer la commande "symfony serve" dans votre terminal


# Contribute
Yohann CLEMENT
Tristan NAVEZ


