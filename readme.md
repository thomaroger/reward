# 🎁 Reward

Reward est une application de gestion de points destinée aux enfants. Elle permet d’attribuer des points (positifs ou négatifs) en fonction d’actions définies dans un backoffice. Ces points peuvent ensuite être utilisés par les enfants pour accéder à des activités.

---

## 🚀 Fonctionnalités

- Attribution de points à des enfants selon des actions paramétrables.
- Interface d'administration pour configurer les actions et leur valeur en points.
- Déduction automatique des points lorsqu’un enfant choisit une activité.
- Suivi du solde de points pour chaque enfant.
- Gestion des activités échangeables contre des points.

---

## 🧰 Stack technique

- **Backend** : Symfony (PHP)
- **Frontend** : Twig
- **Base de données** : MariaDB

---

## ⚙️ Installation

### Prérequis

- PHP >= 8.2
- Composer
- Serveur MariaDB
- Symfony CLI (optionnel mais recommandé)

### Étapes

1. Clonez le projet :
   ```bash
   git clone https://github.com/thomaroger/reward.git
   cd reward
   ```

2. Copiez le fichier d’environnement :
   ```bash
   cp .env.dist .env
   ```

3. Modifiez le fichier `.env` pour y renseigner :
   - `APP_SECRET`
   - `DATABASE_URL` : en fonction du login, mot de passe, hôte et port de votre serveur MariaDB.

   Exemple :
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/reward_db"
   APP_SECRET=un_secret_aleatoire
   ```

4. Installez les dépendances PHP :
   ```bash
   composer update
   ```

5. Créez la base de données et lancez les migrations :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. (Optionnel) Chargez des données de test :

7. Lancez le serveur de développement :
   ```bash
   symfony server:start
   ```

---

## 🛠️ Configuration

### Variables d’environnement importantes

| Variable       | Description                              |
|----------------|------------------------------------------|
| `APP_SECRET`   | Clé secrète de l’application Symfony     |
| `DATABASE_URL` | URL de connexion à la base de données    |

---

## 📁 Structure rapide du projet

```
.
├── config/         # Configuration Symfony
├── public/         # Point d’entrée web
├── src/            # Code PHP (contrôleurs, entités, etc.)
├── templates/      # Fichiers Twig
├── migrations/     # Migrations de base de données
├── .env            # Variables d’environnement
└── ...
```

---

## 📸 Captures d'écran


---

## 👤 Auteur

- Thomas Roger 

---

## 📄 Licence

Projet privé – non redistribuable.

---

