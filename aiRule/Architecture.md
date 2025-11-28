## 📍 Clean Architecture - Explication Approfondie

### Principe de Base

La Clean Architecture organise le code en **cercles concentriques** où les dépendances pointent **toujours vers l'intérieur**.

```
╭───────────────────────────────────────────────────────╮
│   ┌─────────────────────────────────────────────┐   │
│   │   ┌───────────────────────────────────┐   │   │
│   │   │         DOMAIN LAYER          │   │   │
│   │   │  (Entities, Repositories)  │   │   │
│   │   │     Logique Métier Pure      │   │   │
│   │   │   ⚠️ AUCUNE DÉPENDANCE        │   │   │
│   │   └───────────────────────────────────┘   │   │
│   │                                             │   │
│   │         APPLICATION LAYER                │   │
│   │      (Use Cases, DTOs, Validators)       │   │
│   │    Dépend uniquement de Domain ↑         │   │
│   └─────────────────────────────────────────────┘   │
│                                                     │
│          INFRASTRUCTURE LAYER                      │
│   (Repositories SQL/File, JWT, PDF, Database)      │
│      Implémente les interfaces ↑                    │
│                                                     │
│          PRESENTATION LAYER                        │
│    (Controllers API/Web, Middleware, Views)        │
│      Utilise Application via Use Cases ↑           │
╰───────────────────────────────────────────────────────╯
```

### Les Flèches de Dépendance

```
Presentation → Application → Domain
                ↑              ↑
        Infrastructure → Domain (implémente interfaces)
```

**Règle** : Les couches externes dépendent des couches internes, **JAMAIS l'inverse**.

---

## 📦 Domain Layer (Cœur)

### 🎯 Objectif

Contenir la **logique métier pure**, indépendante de toute technologie.

### 📂 Structure

```
Domain/
├── Entities/
│   ├── User.php
│   ├── Owner.php
│   ├── Parking.php
│   ├── Reservation.php
│   ├── Stationnement.php
│   ├── Subscription.php
│   └── TarifHoraire.php
│
├── Repositories/          # ⚠️ INTERFACES UNIQUEMENT
│   ├── UserRepositoryInterface.php
│   ├── OwnerRepositoryInterface.php
│   ├── ParkingRepositoryInterface.php
│   ├── ReservationRepositoryInterface.php
│   ├── StationnementRepositoryInterface.php
│   └── SubscriptionRepositoryInterface.php
│
└── Exceptions/
    ├── UserNotFoundException.php
    ├── InvalidCredentialsException.php
    ├── ParkingFullException.php
    ├── InvalidReservationException.php
    ├── ReservationNotFoundException.php
    ├── UnauthorizedAccessException.php
    └── StationnementAlreadyActiveException.php
```

### ✅ Ce qui est AUTORISÉ dans Domain

```php
<?php
namespace Domain\Entities;

// ✅ Classes PHP pures
class Parking {
    private string $id;
    private float $latitude;
    private float $longitude;

    // ✅ Méthodes de logique métier
    public function calculatePrice(int $dureeMinutes): float {
        $prix = 0;
        foreach ($this->tarifsHoraires as $tarif) {
            // Logique calcul...
        }
        return $prix;
    }

    // ✅ Validation métier
    public function isOpenAt(int $timestamp): bool {
        // Vérifier horaires...
    }
}
```

### ❌ Ce qui est INTERDIT dans Domain

```php
<?php
namespace Domain\Entities;

use PDO; // ❌ INTERDIT - Dépendance externe
use Infrastructure\Services\Logger; // ❌ INTERDIT - Couche externe

class User {
    private PDO $pdo; // ❌ INTERDIT

    public function save(): void {
        // ❌ INTERDIT - Accès BDD direct
        $this->pdo->prepare("INSERT INTO...");
    }
}
```

### 🔌 Repository Interfaces (Ports)

**Concept clé** : Les interfaces définissent le **contrat** dans Domain, les implémentations sont dans Infrastructure.

```php
<?php
namespace Domain\Repositories;

use Domain\Entities\User;

// Interface définie dans DOMAIN
interface UserRepositoryInterface {
    public function save(User $user): void;
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function delete(string $id): void;
}
```

**Pourquoi dans Domain ?**

-   Le Domain définit **ce dont il a besoin** (le contrat)
-   L'Infrastructure fournit **comment le faire** (l'implémentation)
-   Principe d'**Inversion de Dépendance** (SOLID)

---

## 🧠 Application Layer (Orchestration)

### 🎯 Objectif

Orchestrer les **cas d'usage** de l'application en utilisant les entités du Domain.

### 📂 Structure

```
Application/
├── UseCases/
│   ├── Auth/
│   │   ├── RegisterUserUseCase.php
│   │   ├── RegisterOwnerUseCase.php
│   │   └── LoginUseCase.php
│   │
│   ├── Owner/
│   │   ├── CreateParkingUseCase.php
│   │   ├── UpdateParkingTarifsUseCase.php
│   │   ├── CalculateMonthlyRevenueUseCase.php
│   │   └── ...
│   │
│   ├── User/
│   │   ├── SearchParkingsByGPSUseCase.php
│   │   ├── CreateReservationUseCase.php
│   │   └── ...
│   │
│   ├── Stationnement/
│   │   ├── EnterParkingUseCase.php
│   │   ├── ExitParkingUseCase.php
│   │   ├── CalculateFinalPriceUseCase.php
│   │   └── ApplyPenaltyUseCase.php
│   │
│   └── Subscription/
│       ├── SubscribeUseCase.php
│       └── CheckSubscriptionValidityUseCase.php
│
├── DTOs/
│   ├── Input/
│   │   ├── RegisterUserInput.php
│   │   ├── LoginInput.php
│   │   ├── CreateParkingInput.php
│   │   ├── CreateReservationInput.php
│   │   └── ...
│   │
│   └── Output/
│       ├── UserOutput.php
│       ├── AuthTokenOutput.php
│       ├── ParkingDetailsOutput.php
│       ├── ReservationOutput.php
│       └── ...
│
└── Validators/
    ├── EmailValidator.php
    ├── PasswordValidator.php
    ├── GPSCoordinatesValidator.php
    └── TimeSlotValidator.php
```

### 💡 Anatomie d'un Use Case

```php
<?php
namespace Application\UseCases\User;

use Application\DTOs\Input\CreateReservationInput;
use Application\DTOs\Output\ReservationOutput;
use Application\Validators\TimeSlotValidator;
use Domain\Repositories\UserRepositoryInterface;
use Domain\Repositories\ParkingRepositoryInterface;
use Domain\Repositories\ReservationRepositoryInterface;
use Domain\Entities\Reservation;
use Domain\Exceptions\ParkingFullException;

class CreateReservationUseCase {
    // ✅ INJECTION D'INTERFACES (pas d'implémentations)
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private TimeSlotValidator $validator;

    public function __construct(
        UserRepositoryInterface $userRepo,
        ParkingRepositoryInterface $parkingRepo,
        ReservationRepositoryInterface $reservationRepo,
        TimeSlotValidator $validator
    ) {
        $this->userRepository = $userRepo;
        $this->parkingRepository = $parkingRepo;
        $this->reservationRepository = $reservationRepo;
        $this->validator = $validator;
    }

    public function execute(CreateReservationInput $input): ReservationOutput {
        // 1. VALIDATION
        $this->validator->validate($input->debut, $input->fin);

        // 2. RÉCUPÉRATION ENTITÉS
        $user = $this->userRepository->findById($input->userId);
        $parking = $this->parkingRepository->findById($input->parkingId);

        // 3. VÉRIFICATION DISPONIBILITÉ
        $reservationsActives = $this->reservationRepository
            ->findActiveByParking($parking->getId(), $input->debut, $input->fin);

        if (count($reservationsActives) >= $parking->getNbPlaces()) {
            throw new ParkingFullException();
        }

        // 4. LOGIQUE MÉTIER (via Entity)
        $prixEstime = $parking->calculatePrice(
            ($input->fin - $input->debut) / 60
        );

        // 5. CRÉATION ENTITÉ
        $reservation = new Reservation(
            id: uniqid(),
            userId: $input->userId,
            parkingId: $input->parkingId,
            debut: $input->debut,
            fin: $input->fin,
            prixEstime: $prixEstime,
            statut: 'active'
        );

        // 6. PERSISTENCE (via interface)
        $this->reservationRepository->save($reservation);

        // 7. RETOUR DTO OUTPUT
        return new ReservationOutput(
            id: $reservation->getId(),
            parkingNom: $parking->getNom(),
            debut: $reservation->getDebut(),
            fin: $reservation->getFin(),
            prixEstime: $reservation->getPrixEstime(),
            statut: $reservation->getStatut()
        );
    }
}
```

### 📦 DTOs (Data Transfer Objects)

**Objectif** : Découpler les couches en transférant uniquement les données nécessaires.

```php
<?php
namespace Application\DTOs\Input;

// Input DTO : Données entrantes
class CreateReservationInput {
    public function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly int $debut,
        public readonly int $fin
    ) {}
}
```

```php
<?php
namespace Application\DTOs\Output;

// Output DTO : Données sortantes
class ReservationOutput {
    public function __construct(
        public readonly string $id,
        public readonly string $parkingNom,
        public readonly int $debut,
        public readonly int $fin,
        public readonly float $prixEstime,
        public readonly string $statut
    ) {}
}
```

**Pourquoi des DTOs ?**

-   ✅ **Découplage** : Entities peuvent évoluer sans casser l'API
-   ✅ **Sécurité** : Exposition contrôlée des données
-   ✅ **Simplicité** : Données plates, faciles à sérialiser

---

## 🔧 Infrastructure Layer (Implémentations)

### 🎯 Objectif

Fournir les **implémentations concrètes** des interfaces définies dans Domain.

### 📂 Structure

```
Infrastructure/
├── Persistence/
│   ├── DatabaseConnection.php
│   │
│   ├── SQL/                   # Implémentations MySQL
│   │   ├── MySQLUserRepository.php
│   │   ├── MySQLOwnerRepository.php
│   │   ├── MySQLParkingRepository.php
│   │   ├── MySQLReservationRepository.php
│   │   ├── MySQLStationnementRepository.php
│   │   └── MySQLSubscriptionRepository.php
│   │
│   └── File/                  # Implémentations JSON (alternatif)
│       ├── FileUserRepository.php
│       ├── FileParkingRepository.php
│       └── FileReservationRepository.php
│
├── Security/
│   ├── JWTService.php
│   └── PasswordHasher.php
│
└── Services/
    └── PDFInvoiceGenerator.php
```

### 💾 Repository SQL Implementation

```php
<?php
namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\UserRepositoryInterface; // ✅ Implémente interface
use Domain\Entities\User;
use PDO;

class MySQLUserRepository implements UserRepositoryInterface {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(User $user): void {
        // ✅ Prepared statements TOUJOURS
        $stmt = $this->pdo->prepare("
            INSERT INTO users (id, email, password, nom, prenom)
            VALUES (:id, :email, :password, :nom, :prenom)
            ON DUPLICATE KEY UPDATE
                email = :email,
                nom = :nom,
                prenom = :prenom
        ");

        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom()
        ]);
    }

    public function findById(string $id): ?User {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        // Reconstruire l'Entity
        return new User(
            id: $data['id'],
            email: $data['email'],
            password: $data['password'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }

    public function findByEmail(string $email): ?User {
        // ...
    }

    public function delete(string $id): void {
        // ...
    }
}
```

### 📝 Repository File Implementation

```php
<?php
namespace Infrastructure\Persistence\File;

use Domain\Repositories\UserRepositoryInterface; // Même interface
use Domain\Entities\User;

class FileUserRepository implements UserRepositoryInterface {
    private string $filePath;

    public function __construct(string $dataDirectory) {
        $this->filePath = $dataDirectory . '/users.json';
    }

    public function save(User $user): void {
        $users = $this->loadAll();
        $users[$user->getId()] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom()
        ];

        file_put_contents(
            $this->filePath,
            json_encode($users, JSON_PRETTY_PRINT)
        );
    }

    public function findById(string $id): ?User {
        $users = $this->loadAll();

        if (!isset($users[$id])) return null;

        $data = $users[$id];
        return new User(
            id: $data['id'],
            email: $data['email'],
            password: $data['password'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }

    private function loadAll(): array {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    // ...
}
```

### 🔄 Interchangeabilité Garantie

**Point crucial** : Les Use Cases ne savent PAS quelle implémentation est utilisée.

```php
// Configuration - On choisit l'implémentation
$config = require __DIR__ . '/config/database.php';

if ($config['storage'] === 'sql') {
    $userRepo = new MySQLUserRepository($pdo);
} else {
    $userRepo = new FileUserRepository($config['data_dir']);
}

// Le Use Case reçoit l'interface, peu importe l'implémentation
$useCase = new RegisterUserUseCase(
    $userRepo,      // ✅ Interface, pas implémentation
    $passwordHasher
);

// ➡️ Le code du Use Case ne change JAMAIS
```

---

## 🌐 Presentation Layer (Controllers)

### 🎯 Objectif

Gérer les **requêtes HTTP** et retourner les **réponses** (JSON ou HTML).

### 📂 Structure

```
Presentation/
├── Api/
│   └── Controllers/
│       ├── UserApiController.php
│       └── OwnerApiController.php
│
├── Web/
│   ├── Controllers/
│   │   ├── UserWebController.php
│   │   └── OwnerWebController.php
│   │
│   └── Views/
│       ├── layout.php
│       ├── auth/
│       ├── user/
│       └── owner/
│
└── Middleware/
    └── AuthMiddleware.php
```

### 📱 API Controller (JSON)

```php
<?php
namespace Presentation\Api\Controllers;

use Application\UseCases\User\CreateReservationUseCase;
use Application\DTOs\Input\CreateReservationInput;
use Domain\Exceptions\ParkingFullException;

class UserApiController {
    private CreateReservationUseCase $createReservationUseCase;

    public function __construct(CreateReservationUseCase $useCase) {
        $this->createReservationUseCase = $useCase;
    }

    // POST /api/reservations
    public function createReservation(): void {
        // 1. PARSING REQUEST
        $data = json_decode(file_get_contents('php://input'), true);

        // 2. CRÉER INPUT DTO
        $input = new CreateReservationInput(
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            debut: $data['debut'],
            fin: $data['fin']
        );

        try {
            // 3. APPELER USE CASE
            $output = $this->createReservationUseCase->execute($input);

            // 4. RETOURNER JSON
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'reservation' => [
                    'id' => $output->id,
                    'parking_nom' => $output->parkingNom,
                    'debut' => $output->debut,
                    'fin' => $output->fin,
                    'prix_estime' => $output->prixEstime
                ]
            ]);
        } catch (ParkingFullException $e) {
            // 5. GESTION ERREURS
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Parking complet']);
        }
    }
}
```

**Rôle du Controller** :

1. ✅ Parser la requête HTTP
2. ✅ Créer Input DTO
3. ✅ Appeler Use Case
4. ✅ Transformer Output DTO en Response
5. ✅ Gérer les exceptions

**Ce qu'un Controller NE DOIT PAS faire** :

-   ❌ Calculer le prix (logique métier)
-   ❌ Accéder à la BDD directement
-   ❌ Valider les données (le Use Case valide)

---

## 🔗 Injection de Dépendances

### Configuration Manuelle (Simple)

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

// 1. CONFIGURATION
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO($config['dsn'], $config['user'], $config['password']);

// 2. REPOSITORIES (Infrastructure)
$userRepo = new MySQLUserRepository($pdo);
$parkingRepo = new MySQLParkingRepository($pdo);
$reservationRepo = new MySQLReservationRepository($pdo);

// 3. SERVICES (Infrastructure)
$jwtService = new JWTService($config['jwt_secret']);
$passwordHasher = new PasswordHasher();

// 4. VALIDATORS (Application)
$timeSlotValidator = new TimeSlotValidator();

// 5. USE CASES (Application)
$createReservationUseCase = new CreateReservationUseCase(
    $userRepo,         // ✅ Interface injectée
    $parkingRepo,      // ✅ Interface injectée
    $reservationRepo,  // ✅ Interface injectée
    $timeSlotValidator
);

// 6. CONTROLLERS (Presentation)
$userApiController = new UserApiController(
    $createReservationUseCase
);

// 7. ROUTING
$router = new Router();
$router->post('/api/reservations', [$userApiController, 'createReservation']);
$router->dispatch();
```

### Pourquoi cette approche fonctionne ?

✅ Les Use Cases reçoivent des **interfaces**

✅ On peut changer `MySQLUserRepository` par `FileUserRepository` sans toucher aux Use Cases

✅ Testable facilement avec des mocks

---

## 🧪 Tests avec Mocks

### Tester un Use Case

```php
<?php
namespace Tests\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Application\UseCases\User\CreateReservationUseCase;
use Application\DTOs\Input\CreateReservationInput;
use Domain\Repositories\UserRepositoryInterface;
use Domain\Entities\User;
use Domain\Exceptions\ParkingFullException;

class CreateReservationUseCaseTest extends TestCase {
    public function testThrowsExceptionWhenParkingFull(): void {
        // ARRANGE - Créer des MOCKS des repositories
        $userRepoMock = $this->createMock(UserRepositoryInterface::class);
        $parkingRepoMock = $this->createMock(ParkingRepositoryInterface::class);
        $reservationRepoMock = $this->createMock(ReservationRepositoryInterface::class);

        // Configurer le comportement des mocks
        $userRepoMock->method('findById')
            ->willReturn(new User('123', '[test@example.com](mailto:test@example.com)', 'hash', 'Doe', 'John'));

        $parkingMock = $this->createMock(Parking::class);
        $parkingMock->method('getNbPlaces')->willReturn(10);
        $parkingRepoMock->method('findById')->willReturn($parkingMock);

        // Simuler parking plein
        $reservationRepoMock->method('findActiveByParking')
            ->willReturn(array_fill(0, 10, new Reservation(/* ... */)));

        $useCase = new CreateReservationUseCase(
            $userRepoMock,
            $parkingRepoMock,
            $reservationRepoMock,
            new TimeSlotValidator()
        );

        $input = new CreateReservationInput(
            userId: '123',
            parkingId: '456',
            debut: time(),
            fin: time() + 3600
        );

        // ACT & ASSERT
        $this->expectException(ParkingFullException::class);
        $useCase->execute($input);
    }
}
```

**Avantage des mocks** : On teste la LOGIQUE sans accéder à la vraie BDD.

---

## ✅ Checklist Architecture Clean

Avant chaque commit, vérifier :

-   [ ] Repository interfaces dans `Domain/Repositories/`
-   [ ] Use Cases importent UNIQUEMENT interfaces
-   [ ] Aucune classe Infrastructure importée dans Domain
-   [ ] Aucune classe Application importée dans Domain
-   [ ] Pas de SQL dans Use Cases
-   [ ] Pas de logique métier dans Controllers
-   [ ] DTOs utilisés pour Input/Output
-   [ ] Interchangeabilité SQL/File vérifiée
-   [ ] Tests avec mocks (pas de vraie BDD)

---

**Prochaine lecture** : [API.md](http://API.md) pour la documentation des endpoints REST.
