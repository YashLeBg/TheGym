# TheGym - Suivi d'Entraînements Sportifs et Coaching

Ce projet vise à développer une application web pour la gestion d'un club de sport axé sur le coaching personnalisé.

## Fonctionnalités

- **Un back-office** (Symfony + EasyAdmin) pour les coachs et responsables du club.
- **Une API REST** (Symfony) permettant l'interaction entre le front et le back.
- **Un front-office** (Angular) pour les sportifs, leur permettant de réserver et suivre leurs entraînements.

## Technologies

- PHP 8.4
- Symfony (CLI) 5.10
- Composer 2.8
- Node 20
- npm 10
- Angular (CLI) 18

## Installation

### Back-end

1. Cloner le projet

```bash
git clone https://github.com/bastos-rcd/TheGym.git
cd TheGym
```

2. Installer les dépendances

```bash
cd backend
composer install
```

3. Configurer les variables d'environnement (`.env`)

```text
APP_ENV=dev
APP_SECRET=

# --------------------------------------------------
# Choisir une URL pour le serveur de base de données
# --------------------------------------------------
# DATABASE_URL="mysql://root:root@127.0.0.1:3306/gymdb?serverVersion=10.8.3&charset=utf8mb4"
# DATABASE_URL="mysql://root:root@127.0.0.1:3306/gymdb?serverVersion=10.8.3-MariaDB&charset=utf8mb4"
# --------------------------------------------------

CORS_ALLOW_ORIGIN='^http?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=poop_my_gym
```

4. Lancer la base de données et exécuter les migrations et les fixtures

```bash
# Créer la base de données avec votre méthode préférée
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

5. Démarrer le serveur Symfony

```bash
symfony server:start --port=8008
```

### Front-end

1. Installer les dépendances

```bash
cd frontend
npm install
```

2. Démarrer l'application

```bash
ng serve
```

## Utilisation

- **Back-office** : Accessible via `http://localhost:8008/admin`
- **API REST** : Disponible sur `http://localhost:8008/api`
- **Front-end** : Accessible via `http://localhost:4200`

## Auteurs

- **[Bastien Record](https://github.com/bastos-rcd)**
- **[Anli-Yachourti Mohamed](https://github.com/yashlebg)**
- **[Jimmy Mauriac](https://github.com/jimmy-txi)**

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
