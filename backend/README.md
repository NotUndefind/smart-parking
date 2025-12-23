# Smart Parkings - Backend

Application de gestion de parkings partagés en **PHP 8.2+ pur** (Clean Architecture).

## 🚀 Démarrage Rapide

```bash
# Installation
composer install

# Lancer les tests
vendor/bin/phpunit

# Démarrer le serveur
php -S localhost:8000 -t public/
```

## 📋 Pré-requis

- **PHP** ≥ 8.2 (avec `ext-json`, `ext-pdo`)
- **Composer** ≥ 2.0
- **MySQL** ≥ 8.0 (optionnel, mode SQL uniquement)

## ⚙️ Configuration

### Mode de stockage

```bash
# Par défaut : fichiers JSON (aucune DB requise)
export STORAGE_TYPE=file

# Mode MySQL
export STORAGE_TYPE=sql
```

### Base de données (mode SQL uniquement)

1. Créer la base :
```bash
mysql -u root -p -e "CREATE DATABASE smart_parking"
```

2. Éditer `config/database.php` avec vos credentials

### JWT

Éditer `config/jwt.php` pour changer la clé secrète en production.

## 🧪 Tests

```bash
# Tous les tests
vendor/bin/phpunit

# Avec couverture
vendor/bin/phpunit --coverage-text

# Rapport HTML
vendor/bin/phpunit --coverage-html coverage/
```

**Couverture actuelle** : 78.24% (objectif 60% atteint ✅)

## 📡 API Endpoints

### Auth (public)
- `POST /api/auth/register/user` - Inscription utilisateur
- `POST /api/auth/login` - Connexion
- `POST /api/auth/register/owner` - Inscription propriétaire

### User (🔒 JWT requis)
- `GET /api/parkings/search` - Rechercher parkings
- `POST /api/reservations` - Créer réservation
- `POST /api/stationnements/enter` - Entrer parking
- `POST /api/stationnements/exit` - Sortir parking
- `POST /api/invoices/generate` - Générer facture

### Owner (🔒 JWT requis)
- `POST /api/owner/parkings` - Créer parking
- `PUT /api/owner/parkings/{id}/tariff` - Mettre à jour tarifs
- `GET /api/owner/parkings/{id}/revenue` - Chiffre d'affaires

**Auth** : Header `Authorization: Bearer <token>`

## 🏗️ Architecture

```
src/
├── Domain/              # Entités, interfaces, logique métier
├── Application/         # Use cases, DTOs, validators
├── Infrastructure/      # Implémentations (SQL, fichiers, JWT)
└── Presentation/        # API controllers, middleware
```

**Principe** : Dépendances vers l'intérieur uniquement (Domain → Application → Infrastructure/Presentation)

## 📚 Documentation

- `aiRule/Architecture.md` - Détails architecture Clean
- `aiRule/Development.md` - Conventions, workflow Git
- `aiRule/GlobalContext.md` - Règles métier, barème

## 🔒 Sécurité

✅ JWT (`firebase/php-jwt`)
✅ Bcrypt (`password_hash`)
✅ Prepared statements (SQL)
✅ Validators (Email, Password, GPS)

## 📊 Barème Projet

- 12 pts : Fonctionnalités complètes
- **4 pts : Tests PHPUnit (60% couverture) ✅**
- 2 pts : Authentification JWT ✅
- 2 pts : Architecture Clean ✅

**Deadline** : 22 décembre 2025, 23h59

---

**Version** : 1.0 | **Équipe** : 4 personnes
