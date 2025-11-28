## 🎯 Vue d'Ensemble du Projet

### Objectif

Développer une **application de parking partagé** en PHP 8.x pur (sans framework) permettant aux propriétaires de louer leurs places inoccupées et aux utilisateurs de réserver/utiliser ces places.

### Contraintes Académiques

-   **Équipe** : 4 personnes
-   **Deadline** : 22 décembre 2025, 23h59
-   **Pénalité** : -2 points/jour de retard
-   **Architecture obligatoire** : Clean Architecture
-   **Tests obligatoires** : PHPUnit avec 60%+ couverture
-   **Authentification** : JWT obligatoire
-   **Stockage** : Multi-sources (SQL + NoSQL/Fichiers)

### Barème (20 points)

-   **12 points** : Fonctionnalités complètes
-   **4 points** : Tests PHPUnit (60% couverture Domain + Use Cases)
-   **2 points** : Authentification JWT
-   **2 points** : Architecture Clean
-   **Jusqu'à -4 points** : Qualité code (conventions, lisibilité)

---

## 🏗️ Architecture Clean - Principes Fondamentaux

### Structure des Couches

```
src/
├── Domain/              # ❤️ CŒUR - Logique métier pure
│   ├── Entities/        # Objets métier (User, Parking, Reservation...)
│   ├── Repositories/    # ⚠️ INTERFACES uniquement (Ports)
│   └── Exceptions/      # Exceptions métier
│
├── Application/         # 🧠 ORCHESTRATION - Use Cases
│   ├── UseCases/        # Cas d'usage (RegisterUser, CreateReservation...)
│   ├── DTOs/            # Data Transfer Objects (Input/Output)
│   └── Validators/      # Validateurs (Email, Password, GPS...)
│
├── Infrastructure/      # 🔧 TECHNIQUE - Implémentations
│   ├── Persistence/     # Implémentations Repository
│   │   ├── SQL/         # MySQL/PostgreSQL/SQLite
│   │   └── File/        # JSON/NoSQL (alternatif requis)
│   ├── Security/        # JWT, PasswordHasher
│   └── Services/        # PDF, Email, etc.
│
└── Presentation/        # 🌐 INTERFACE - Controllers
    ├── Api/             # REST API (JSON)
    │   └── Controllers/ # UserApiController, OwnerApiController
    ├── Web/             # Interface HTML (optionnel)
    │   ├── Controllers/ # UserWebController, OwnerWebController
    │   └── Views/       # Templates HTML
    └── Middleware/      # AuthMiddleware
```

### Règles d'Or (NON NÉGOCIABLES)

### ✅ CE QU'IL FAUT FAIRE

1. **Domain est INDÉPENDANT**
    - ❌ Aucun import de classes externes (pas de PDO, pas de librairies)
    - ✅ Uniquement des classes PHP pures
    - ✅ Logique métier pure uniquement
2. **Interfaces Repository DANS Domain**

    ```php
    // ✅ CORRECT
    src/Domain/Repositories/UserRepositoryInterface.php

    // ❌ FAUX
    src/Infrastructure/Repositories/UserRepositoryInterface.php
    ```

3. **Use Cases utilisent UNIQUEMENT les interfaces**

    ```php
    // ✅ CORRECT
    use Domain\Repositories\UserRepositoryInterface;

    public function __construct(UserRepositoryInterface $userRepo) {
        $this->userRepository = $userRepo;
    }

    // ❌ FAUX - Ne JAMAIS importer l'implémentation
    use Infrastructure\Persistence\SQL\MySQLUserRepository;
    ```

4. **Dependency Inversion (Injection de dépendances)**
    - Les Use Cases reçoivent les interfaces en paramètre constructeur
    - L'implémentation concrète est injectée au runtime
    - Permet l'interchangeabilité SQL ↔ File
5. **DTOs pour découpler les couches**
    - Input DTOs : données entrantes (requêtes)
    - Output DTOs : données sortantes (réponses)
    - Jamais passer des Entities aux Controllers

### ❌ CE QU'IL NE FAUT PAS FAIRE

1. **PAS de SQL dans Use Cases**

    ```php
    // ❌ FAUX
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");

    // ✅ CORRECT
    $user = $this->userRepository->findByEmail($email);
    ```

2. **PAS de logique métier dans Controllers**

    ```php
    // ❌ FAUX - Calcul dans Controller
    $prix = $parking->tarif * $duree;

    // ✅ CORRECT - Déléguer au Use Case
    $output = $this->calculatePriceUseCase->execute($input);
    ```

3. **PAS de dépendances Domain → Infrastructure**

    ```php
    // ❌ FAUX - Domain ne peut pas importer Infrastructure
    namespace Domain\Entities;
    use Infrastructure\Services\Logger; // INTERDIT
    ```

4. **PAS d'Entity directement dans Response**

    ```php
    // ❌ FAUX
    return json_encode($user); // $user est une Entity

    // ✅ CORRECT
    return json_encode($userOutput); // $userOutput est un DTO
    ```

---

## 🔄 Flux de Données Standard

### Exemple : Créer une Réservation

```
1. HTTP REQUEST
   POST /api/reservations
   Body: {"user_id": "123", "parking_id": "456", "debut": 1234567890, "fin": 1234571490}
   ↓

2. CONTROLLER (Presentation)
   UserApiController::createReservation()
   - Récupère données JSON
   - Crée CreateReservationInput DTO
   ↓

3. USE CASE (Application)
   CreateReservationUseCase::execute(CreateReservationInput $input)
   - Valide avec TimeSlotValidator
   - Récupère User via UserRepositoryInterface
   - Récupère Parking via ParkingRepositoryInterface
   - Vérifie disponibilité (compte réservations actives)
   - Crée Entity Reservation avec logique métier
   - Sauvegarde via ReservationRepositoryInterface
   - Retourne ReservationOutput DTO
   ↓

4. REPOSITORY INTERFACE (Domain)
   ReservationRepositoryInterface::save(Reservation $reservation)
   - Contrat défini dans Domain
   ↓

5. REPOSITORY IMPLEMENTATION (Infrastructure)
   MySQLReservationRepository::save(Reservation $reservation)
   - Implémentation SQL concrète
   - PDO avec prepared statements
   - INSERT INTO reservations...
   ↓

6. RESPONSE (Controller)
   - Reçoit ReservationOutput DTO
   - Transforme en JSON
   - http_response_code(201)
   - echo json_encode($output)
```

### Points Clés du Flux

✅ **Controller** → crée Input DTO → appelle Use Case

✅ **Use Case** → utilise Repository Interface → retourne Output DTO

✅ **Repository Interface** → définie dans Domain

✅ **Repository Implementation** → implémente l'interface dans Infrastructure

✅ **Controller** → transforme Output DTO en Response HTTP

---

## 📦 Entités Métier Principales

### User (Utilisateur)

```php
class User {
    private string $id;
    private string $email;
    private string $password; // hashé avec password_hash()
    private string $nom;
    private string $prenom;
    private array $reservations; // Reservation[]
    private array $stationnements; // Stationnement[]
}
```

### Owner (Propriétaire)

```php
class Owner {
    private string $id;
    private string $email;
    private string $password;
    private string $nom;
    private string $prenom;
    private array $parkings; // Parking[]
}
```

### Parking

```php
class Parking {
    private string $id;
    private float $latitude;
    private float $longitude;
    private int $nbPlaces;
    private array $tarifsHoraires; // TarifHoraire[]
    private array $horairesOuverture; // ["lundi" => ["08:00-18:00"], ...]
    private array $reservations; // Reservation[]
    private array $stationnements; // Stationnement[]
}
```

### Reservation

```php
class Reservation {
    private string $id;
    private string $userId;
    private string $parkingId;
    private int $debut; // timestamp
    private int $fin; // timestamp
    private float $prixEstime;
    private string $statut; // "active", "terminee", "annulee"
}
```

### Stationnement

```php
class Stationnement {
    private string $id;
    private string $userId;
    private string $parkingId;
    private int $debut; // timestamp
    private ?int $fin; // timestamp (null si en cours)
    private float $montantFacture;
    private float $penalite; // 20€ si hors créneau
}
```

### Subscription (Abonnement)

```php
class Subscription {
    private string $id;
    private string $userId;
    private string $parkingId;
    private array $creneauxReserves; // [{"jour": "lundi", "debut": "08:00", "fin": "18:00"}, ...]
    private int $dateDebut; // timestamp
    private int $dateFin; // timestamp
    private float $prixMensuel;
    private string $type; // "total", "weekend", "soir", "personnalise"
}
```

### TarifHoraire

```php
class TarifHoraire {
    private int $trancheDuree; // en minutes (ex: 15, 30, 60)
    private float $prix; // en euros
    private int $ordre; // pour gérer les tarifs dégressifs
}
```

---

## 🎯 Règles Métier Critiques

### 1. Gestion des Places Disponibles

**Règle** : Le système doit maintenir en temps réel le nombre de places disponibles.

-   Réservation active = -1 place pendant le créneau
-   Abonnement actif = -1 place pendant les créneaux réservés (même si absent)
-   Stationnement actif = -1 place
-   Une réservation est **refusée** si le parking est plein à un moment du créneau demandé

**Algorithme de vérification** :

```php
// Pseudo-code
function checkAvailability(Parking $parking, int $debut, int $fin): bool {
    $placesOccupees = 0;

    // Compter réservations actives pendant le créneau
    foreach ($reservationsActives as $reservation) {
        if (overlap($reservation->debut, $reservation->fin, $debut, $fin)) {
            $placesOccupees++;
        }
    }

    // Compter abonnements actifs pendant le créneau
    foreach ($subscriptionsActives as $subscription) {
        if (subscriptionCoversTimeSlot($subscription, $debut, $fin)) {
            $placesOccupees++;
        }
    }

    // Compter stationnements en cours
    $placesOccupees += count($stationnementsActifs);

    return ($parking->nbPlaces - $placesOccupees) > 0;
}
```

### 2. Pénalités de Stationnement

**Règle** : Pénalité de **20€** si dépassement du créneau de réservation/abonnement.

```php
// Si fin réelle > fin prévue
if ($finReelle > $reservation->fin) {
    $penalite = 20.00;
    $tempsSupplementaire = $finReelle - $reservation->fin;
    $prixSupplementaire = calculerPrix($tempsSupplementaire, $parking->tarifs);
    $montantTotal = $prixReservation + $penalite + $prixSupplementaire;
}
```

### 3. Calcul Tarif Horaire

**Règle** : Facturation par tranches de **15 minutes**, tarifs dégressifs possibles.

```php
// Exemple tarification
// 0-1h : 2€/15min
// 1-3h : 1.50€/15min
// 3h+ : 1€/15min

function calculatePrice(int $dureeMinutes, array $tarifs): float {
    $prix = 0;
    $minutesRestantes = $dureeMinutes;

    foreach ($tarifs as $tranche) {
        $minutesDansTranche = min($minutesRestantes, $tranche->trancheDuree);
        $nbTranches = ceil($minutesDansTranche / 15);
        $prix += $nbTranches * $tranche->prix;
        $minutesRestantes -= $minutesDansTranche;

        if ($minutesRestantes <= 0) break;
    }

    return $prix;
}
```

### 4. Horaires d'Ouverture

**Règle** : Un parking peut avoir des horaires spécifiques ou être ouvert 24/7.

```php
// Exemples
$parking1->horaires = ["24/7" => true]; // Toujours ouvert

$parking2->horaires = [
    "lundi" => ["08:00-18:00"],
    "mardi" => ["08:00-18:00"],
    "vendredi" => ["18:00-23:59"],
    "samedi" => ["00:00-23:59"],
    "dimanche" => ["00:00-08:00"]
]; // Week-end uniquement
```

### 5. Abonnements Flexibles

**Types d'abonnements** :

-   **Total** : Accès illimité 24/7
-   **Week-end** : Vendredi 18h → Lundi 10h
-   **Soir** : Tous les soirs 18h → 8h lendemain
-   **Personnalisé** : Créneaux spécifiques définis

**Règle** : Les créneaux d'abonnement sont **fixes sur la semaine** et se répètent.

---

## 🔐 Sécurité

### JWT (JSON Web Token)

**Implémentation requise** :

```php
class JWTService {
    private string $secret; // Défini dans config/jwt.php
    private int $expiration; // Ex: 3600 secondes (1h)

    public function generate(User|Owner $user): string {
        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user instanceof Owner ? 'owner' : 'user',
            'iat' => time(),
            'exp' => time() + $this->expiration
        ];

        // Utiliser firebase/php-jwt ou implementation custom
        return $this->encode($payload);
    }

    public function validate(string $token): array {
        // Vérifier signature + expiration
        return $this->decode($token);
    }
}
```

**AuthMiddleware** :

```php
class AuthMiddleware {
    public function handle(): void {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }

        $token = $matches[1];

        try {
            $payload = $this->jwtService->validate($token);
            $_REQUEST['user_id'] = $payload['user_id'];
            $_REQUEST['role'] = $payload['role'];
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }
    }
}
```

### Hashage des Mots de Passe

```php
class PasswordHasher {
    public function hash(string $password): string {
        // ✅ Utiliser PHP natif (pas de librairie externe)
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
```

### Protection Injection SQL

```php
// ✅ TOUJOURS utiliser prepared statements
public function findByEmail(string $email): ?User {
    $stmt = $this->pdo->prepare(
        "SELECT * FROM users WHERE email = :email"
    );
    $stmt->execute(['email' => $email]);
    // ...
}

// ❌ JAMAIS de concaténation
$query = "SELECT * FROM users WHERE email = '" . $email . "'"; // DANGER
```

### Protection XSS (dans Views HTML)

```php
// ✅ TOUJOURS échapper l'output
<h1><?= htmlspecialchars($user->getNom(), ENT_QUOTES, 'UTF-8') ?></h1>

// ❌ JAMAIS d'output direct
<h1><?= $user->getNom() ?></h1> // DANGER
```

---

## 📚 Documentation Complémentaire

Consultez ces documents pour plus de détails :

1. [**ARCHITECTURE.md**](http://ARCHITECTURE.md) - Détails techniques de l'architecture
2. [**API.md**](http://API.md) - Documentation complète de l'API REST
3. [**DEVELOPMENT.md**](http://DEVELOPMENT.md) - Guide pratique développement

---

## ⚠️ Points d'Attention pour l'IA

Quand vous aidez au développement, vérifiez TOUJOURS :

✅ Repository interfaces dans `Domain/Repositories/`

✅ Use Cases importent UNIQUEMENT interfaces, pas implémentations

✅ Pas de SQL dans Use Cases

✅ Pas de logique métier dans Controllers

✅ DTOs pour Input/Output (jamais d'Entities directement)

✅ Interchangeabilité SQL ↔ File garantie

✅ Tests unitaires avec mocks des repositories

✅ Prepared statements SQL partout

✅ Hashage bcrypt des mots de passe

✅ Validation JWT sur routes protégées

---

**Version** : 1.0

**Dernière mise à jour** : 28 novembre 2024
