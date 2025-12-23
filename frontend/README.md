# Smart Parking - Frontend

Application web de gestion de parkings intelligents permettant aux utilisateurs de réserver des places et aux propriétaires de gérer leurs parkings.

## Prérequis

- Le backend API doit être démarré sur `http://localhost:8000`

## Démarrage

1. Assurez-vous que le backend est démarré:
   ```bash
   cd ../backend
   php -S localhost:8000 -t public public/router.php
   ```

2. Ouvrez simplement les fichiers HTML dans votre navigateur ou utilisez un serveur web local:
   ```bash
   # Option 1: Avec Python 3
   python -m http.server 3000

   # Option 2: Avec PHP
   php -S localhost:3000

   # Option 3: Avec Node.js (http-server)
   npx http-server -p 3000
   ```

3. Accédez à l'application: `http://localhost:3000`

## Pages disponibles

### Pour les utilisateurs

#### Authentification
- **`index.html`** - Page d'accueil et recherche de parkings (nécessite connexion)
- **`login.html`** - Connexion utilisateur
- **`register.html`** - Inscription utilisateur

#### Fonctionnalités utilisateur
- **`index.html`** - Rechercher des parkings à proximité
- **`parking-details.html`** - Voir les détails d'un parking et faire une réservation
- **`my-reservations.html`** - Gérer vos réservations
- **`my-stationnements.html`** - Historique de vos stationnements
- **`my-subscriptions.html`** - Gérer vos abonnements
- **`my-invoices.html`** - Consulter vos factures

### Pour les propriétaires

#### Authentification
- **`owner-login.html`** - Connexion propriétaire
- **`owner-register.html`** - Inscription propriétaire

#### Gestion
- **`owner-dashboard.html`** - Dashboard propriétaire avec:
  - Liste de vos parkings
  - Création de nouveaux parkings
  - Modification des tarifs
  - Modification des horaires
  - Ajout d'abonnements
  - Statistiques (réservations, revenus)
  - Gestion des dépassements

## Guide d'utilisation

### En tant qu'utilisateur

1. **S'inscrire/Se connecter**
   - Allez sur `register.html` pour créer un compte
   - Ou `login.html` si vous avez déjà un compte
   - Compte de test: `test@gmail.com` / mot de passe défini lors de l'inscription est de Password123

2. **Rechercher un parking**
   - Sur la page d'accueil, cliquez sur "Utiliser ma position"
   - Ou entrez une adresse manuellement
   - Cliquez sur "Rechercher"
   - Les parkings disponibles s'affichent

3. **Réserver une place**
   - Cliquez sur "Voir les détails" d'un parking
   - Choisissez la date et l'heure de début et de fin
   - Cliquez sur "Réserver"
   - Confirmez votre réservation

4. **Gérer vos réservations**
   - Allez sur "Mes réservations"
   - Vous pouvez annuler une réservation en cours

5. **Consulter vos stationnements**
   - Allez sur "Mes stationnements"
   - Voyez l'historique de vos stationnements et les montants payés

6. **Gérer vos abonnements**
   - Allez sur "Mes abonnements"
   - Souscrivez à un abonnement mensuel ou hebdomadaire

### En tant que propriétaire

1. **S'inscrire/Se connecter**
   - Allez sur `owner-register.html` pour créer un compte propriétaire
   - Ou `owner-login.html` si vous avez déjà un compte
   - Compte de test: `owner@test.com` / mot de passe défini lors de l'inscription est Password123

2. **Créer un parking**
   - Dans le dashboard, cliquez sur "Créer un nouveau parking"
   - Remplissez les informations:
     - Nom du parking
     - Adresse
     - **Coordonnées GPS** (latitude entre -90 et 90, longitude entre -180 et 180)
     - Nombre de places
   - Les tarifs sont générés automatiquement (de 15 min à 4h)
   - Cliquez sur "Créer"

3. **Modifier les tarifs**
   - Cliquez sur "Voir les détails" d'un parking
   - Dans la section "Modifier les tarifs", ajoutez ou modifiez les tarifs
   - Chaque tarif a une durée (en minutes) et un prix (en €)

4. **Modifier les horaires**
   - Dans la section "Modifier les horaires"
   - Définissez les heures d'ouverture et de fermeture pour chaque jour
   - Format: HH:MM (ex: 08:00 - 18:00)

5. **Ajouter un abonnement**
   - Dans la section "Ajouter un type d'abonnement"
   - Choisissez le type (hebdomadaire, mensuel, annuel)
   - Définissez le prix

6. **Consulter les statistiques**
   - Cliquez sur "Voir les détails" d'un parking
   - Voyez:
     - Les réservations en cours
     - Les stationnements actifs
     - Le chiffre d'affaires mensuel
     - Les utilisateurs en dépassement

## Structure des fichiers

```
frontend/
├── css/
│   └── style.css              # Styles globaux
├── js/
│   └── api.js                 # Client API et gestion d'authentification
├── index.html                 # Page d'accueil (recherche parkings)
├── login.html                 # Connexion utilisateur
├── register.html              # Inscription utilisateur
├── parking-details.html       # Détails d'un parking
├── my-reservations.html       # Réservations utilisateur
├── my-stationnements.html     # Stationnements utilisateur
├── my-subscriptions.html      # Abonnements utilisateur
├── my-invoices.html           # Factures utilisateur
├── owner-login.html           # Connexion propriétaire
├── owner-register.html        # Inscription propriétaire
├── owner-dashboard.html       # Dashboard propriétaire
└── README.md                  # Ce fichier
```

## Configuration

L'URL de l'API backend est définie dans `js/api.js`:

```javascript
const API_BASE_URL = 'http://localhost:8000/api';
```

Si votre backend tourne sur un autre port ou domaine, modifiez cette variable.

## Fonctionnalités

### Utilisateurs
- Inscription et connexion
- Recherche de parkings par géolocalisation
- Réservation de places
- Gestion des réservations (annulation)
- Historique des stationnements
- Souscription aux abonnements
- Consultation des factures
- Entrée/Sortie de parking

### Propriétaires
- Inscription et connexion
- Création de parkings
- Gestion des tarifs
- Gestion des horaires
- Ajout d'abonnements
- Consultation des réservations
- Consultation des stationnements
- Statistiques de revenus
- Gestion des dépassements

## Notes importantes

### Coordonnées GPS valides
Lors de la création d'un parking, assurez-vous d'entrer des coordonnées GPS valides:
- **Latitude**: entre -90 et 90
- **Longitude**: entre -180 et 180

Exemples de coordonnées valides (Paris):
- Latitude: 48.8566
- Longitude: 2.3522

### Authentification
- Les tokens JWT sont stockés dans `localStorage`
- Durée de validité: 1 heure
- Les utilisateurs et propriétaires ont des espaces séparés

### Recherche de parkings
- La recherche utilise un rayon de 1000 km par défaut
- Les parkings doivent avoir `is_active: true` pour être visibles
- La distance est calculée avec la formule de Haversine

## Dépannage

### Problème: "Aucun parking trouvé"
- Vérifiez que des parkings existent dans le backend
- Vérifiez que les coordonnées GPS des parkings sont valides
- Vérifiez que `is_active` est `true` dans les fichiers JSON

### Problème: "Invalid or expired token"
- Déconnectez-vous et reconnectez-vous
- Vérifiez que le backend utilise la même clé secrète JWT

### Problème: Erreurs CORS
- Vérifiez que le backend est bien démarré avec `router.php`
- Les headers CORS sont configurés dans `backend/public/index.php`

### Problème: Les parkings créés n'apparaissent pas
- Vérifiez les coordonnées GPS (doivent être réalistes)
- Rafraîchissez la page
- Vérifiez que vous êtes connecté avec le bon compte

## Support

Pour signaler un bug ou demander une fonctionnalité, créez une issue sur le dépôt GitHub du projet.

## Licence

Ce projet est développé dans le cadre d'un projet éducatif à HETIC.
