# OCLockMemory

## Informations
- PHP 8
- MySQL
- Symfony 6
- Webpack
- SCSS
- jQuery
- Bootstrap
- etc

## Pour l'utiliser
- composer install
- npm install
- Créer la DB "memory" (pour ma part, avec COLLATE utf8mb4_bin) -> à votre besoin, modifier la ligne 32 du fichier.env
- Lancer le serveur pour Symfony (cmd : symfony server:start)
- Aller sur https://127.0.0.1:8000/ et c'est parti, vous pouvez jouer au mémory

## Explication
Pour ce test, j'ai tenu à inclure l'intelligence applicative coté back. Pour une expérience utilisateur améliorée,
il pourrait être envisageable de faire plus de choses côté front (moins de temps d'attente dans le jeu), mais dans
le cadre du test technique, il m'a semblé plus intéressant de faire pas mal de dev côté back afin d'avoir un peu de
contenu à montrer.
