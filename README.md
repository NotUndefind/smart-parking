# Smart Parkings

Application de gestion de parkings partagés avec backend PHP (Clean Architecture) et frontend HTML/CSS/JS.

## Structure du Projet

```
.
├── backend/     # API REST en PHP 8.2+ (Clean Architecture)
└── frontend/    # Interface utilisateur (HTML/CSS/JS)
```

## Démarrage Rapide

### Backend
```bash
cd backend
composer install
php -S localhost:8000 -t public/
```

Voir [backend/README.md](backend/README.md) pour plus de détails.

### Frontend
```bash
cd frontend
python3 -m http.server 3000
# ou
php -S localhost:3000
```

## Fonctionnalités

- Recherche et réservation de places de parking
- Gestion des stationnements et facturation
- Tableau de bord propriétaire
- Authentification JWT
- Support double stockage (fichiers JSON / MySQL)

## Documentation

- [Backend README](backend/README.md) - API, architecture, tests
- `backend/aiRule/` - Documentation technique détaillée

## Tech Stack

**Backend** : PHP 8.2+, Clean Architecture, PHPUnit
**Frontend** : HTML5, CSS3, JavaScript (Vanilla)
**Auth** : JWT (firebase/php-jwt)
