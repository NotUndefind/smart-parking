# 🛠️ Guide Pratique de Développement

## 🚀 Démarrage Rapide

### 1. Setup Initial (15 minutes)

```bash
# Cloner / Créer le projet
mkdir smart-parking && cd smart-parking
git init

# Créer structure Clean Architecture
mkdir -p src/{Domain,Application,Infrastructure,Presentation}
mkdir -p src/Domain/{Entities,Repositories,Exceptions}
mkdir -p src/Application/{UseCases,DTOs,Validators}
mkdir -p src/Infrastructure/{Persistence,Security,Services}
mkdir -p src/Presentation/{Api,Web,Middleware}

# Initialiser Composer
composer init
```

### 2. Configurer composer.json

```json
{
	"name": "votre-equipe/smart-parking",
	"description": "Application de parking partagé en Clean Architecture",
	"type": "project",
	"require": {
		"php": "^8.0",
		"firebase/php-jwt": "^6.0",
		"dompdf/dompdf": "^2.0"
	},
	"require-dev": {
		"phpunit/phpunit": "^10.0"
	},
	"autoload": {
		"psr-4": {
			"Domain\\": "src/Domain/",
			"Application\\": "src/Application/",
			"Infrastructure\\": "src/Infrastructure/",
			"Presentation\\": "src/Presentation/"
		}
	},
	"autoload-dev": {
		"psr-4": {
			"Tests\\": "tests/"
		}
	}
}
```

```bash
composer install
```

### 3. Configurer PHPUnit

**phpunit.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Domain">
            <directory>tests/Domain</directory>
        </testsuite>
        <testsuite name="Application">
            <directory>tests/Application</directory>
        </testsuite>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src/Domain</directory>
            <directory suffix=".php">src/Application</directory>
        </include>
    </coverage>
</phpunit>
```

### 4. Base de Données

**schema.sql**

```sql
CREATE DATABASE IF NOT EXISTS smart_parking;
USE smart_parking;

CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE owners (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE parkings (
    id VARCHAR(36) PRIMARY KEY,
    owner_id VARCHAR(36) NOT NULL,
    nom VARCHAR(255),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    nb_places INT NOT NULL,
    horaires_ouverture JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES owners(id)
);

CREATE TABLE tarifs_horaires (
    id VARCHAR(36) PRIMARY KEY,
    parking_id VARCHAR(36) NOT NULL,
    tranche_duree INT NOT NULL COMMENT 'en minutes',
    prix DECIMAL(10, 2) NOT NULL,
    ordre INT NOT NULL,
    FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE reservations (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    parking_id VARCHAR(36) NOT NULL,
    debut INT NOT NULL COMMENT 'timestamp',
    fin INT NOT NULL COMMENT 'timestamp',
    prix_estime DECIMAL(10, 2) NOT NULL,
    statut ENUM('active', 'terminee', 'annulee') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE stationnements (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    parking_id VARCHAR(36) NOT NULL,
    debut INT NOT NULL COMMENT 'timestamp',
    fin INT NULL COMMENT 'timestamp, NULL si en cours',
    montant_facture DECIMAL(10, 2),
    penalite DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE subscriptions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    parking_id VARCHAR(36) NOT NULL,
    creneaux_reserves JSON NOT NULL,
    date_debut INT NOT NULL COMMENT 'timestamp',
    date_fin INT NOT NULL COMMENT 'timestamp',
    prix_mensuel DECIMAL(10, 2) NOT NULL,
    type ENUM('total', 'weekend', 'soir', 'personnalise') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE INDEX idx_reservations_active ON reservations(parking_id, statut, debut, fin);
CREATE INDEX idx_stationnements_active ON stationnements(parking_id, fin);
```

---

## 📝 Conventions de Code

### Nommage

```php
// ✅ CamelCase pour classes
class UserRepositoryInterface {}
class CreateReservationUseCase {}

// ✅ camelCase pour méthodes et variables
public function findById(string $userId): ?User {}
$prixEstime = 15.50;

// ✅ SNAKE_CASE pour constantes
const MAX_PLACES_PER_PARKING = 1000;

// ✅ Nom de fichiers = Nom de classe
UserRepositoryInterface.php
CreateReservationUseCase.php
```

### Structure Fonction

```php
// ✅ Fonctions COURTES (< 20 lignes)
public function execute(CreateReservationInput $input): ReservationOutput {
    $this->validate($input);
    $user = $this->getUser($input->userId);
    $parking = $this->getParking($input->parkingId);
    $this->checkAvailability($parking, $input->debut, $input->fin);
    $reservation = $this->createReservation($user, $parking, $input);
    $this->reservationRepository->save($reservation);
    return $this->buildOutput($reservation, $parking);
}

// ❌ TROP LONG (> 20 lignes)
public function execute(CreateReservationInput $input): ReservationOutput {
    // 50 lignes de code...
    // ➡️ Décomposer en méthodes privées
}
```

### PHPDoc

```php
/**
 * Crée une réservation après vérification de disponibilité.
 *
 * @param CreateReservationInput $input Données de réservation
 * @return ReservationOutput
 * @throws ParkingFullException Si aucune place disponible
 * @throws InvalidReservationException Si créneau invalide
 */
public function execute(CreateReservationInput $input): ReservationOutput {
    // ...
}
```

---

## 🔄 Workflow Git

### Branches

```bash
main                    # Production (protégée)
└── develop             # Intégration
    ├── feature/domain-entities
    ├── feature/auth-use-cases
    ├── feature/owner-api
    └── feature/user-reservations
```

### Commits

```bash
# ✅ Messages clairs et précis
git commit -m "feat: Add User entity with validation"
git commit -m "feat: Implement RegisterUserUseCase"
git commit -m "test: Add tests for CreateReservationUseCase"
git commit -m "fix: Correct price calculation in Parking entity"
git commit -m "refactor: Extract validation logic to TimeSlotValidator"

# Préfixes recommandés
feat:     # Nouvelle fonctionnalité
fix:      # Correction bug
test:     # Ajout tests
refactor: # Refactoring (pas de changement fonctionnel)
docs:     # Documentation
style:    # Formatage code
```

---

## 🧪 Écrire des Tests

### Test Unitaire Entity

```php
<?php
namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use Domain\Entities\Parking;
use Domain\Entities\TarifHoraire;

class ParkingTest extends TestCase {
    public function testCalculatePriceWithSimpleTarif(): void {
        // ARRANGE
        $parking = new Parking(
            id: 'p1',
            latitude: 48.8566,
            longitude: 2.3522,
            nbPlaces: 10,
            tarifsHoraires: [
                new TarifHoraire(trancheDuree: 15, prix: 2.00, ordre: 1)
            ],
            horairesOuverture: ['24/7' => true]
        );

        // ACT
        $prix = $parking->calculatePrice(60); // 1 heure

        // ASSERT
        $this->assertEquals(8.00, $prix); // 4 tranches de 15min * 2€
    }

    public function testCalculatePriceWithDegressiveTarif(): void {
        $parking = new Parking(
            id: 'p1',
            latitude: 48.8566,
            longitude: 2.3522,
            nbPlaces: 10,
            tarifsHoraires: [
                new TarifHoraire(trancheDuree: 60, prix: 2.00, ordre: 1),  // 0-1h
                new TarifHoraire(trancheDuree: 120, prix: 1.50, ordre: 2), // 1-3h
            ],
            horairesOuverture: ['24/7' => true]
        );

        $prix = $parking->calculatePrice(180); // 3 heures

        // 1h * 2€ + 2h * 1.50€ = 5€
        $this->assertEquals(5.00, $prix);
    }
}
```

### Test Unitaire Use Case (avec Mocks)

```php
<?php
namespace Tests\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Application\UseCases\User\CreateReservationUseCase;
use Application\DTOs\Input\CreateReservationInput;
use Application\Validators\TimeSlotValidator;
use Domain\Repositories\UserRepositoryInterface;
use Domain\Repositories\ParkingRepositoryInterface;
use Domain\Repositories\ReservationRepositoryInterface;
use Domain\Entities\User;
use Domain\Entities\Parking;
use Domain\Exceptions\ParkingFullException;

class CreateReservationUseCaseTest extends TestCase {
    private $userRepo;
    private $parkingRepo;
    private $reservationRepo;
    private $validator;
    private $useCase;

    protected function setUp(): void {
        // Créer mocks
        $this->userRepo = $this->createMock(UserRepositoryInterface::class);
        $this->parkingRepo = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepo = $this->createMock(ReservationRepositoryInterface::class);
        $this->validator = new TimeSlotValidator();

        $this->useCase = new CreateReservationUseCase(
            $this->userRepo,
            $this->parkingRepo,
            $this->reservationRepo,
            $this->validator
        );
    }

    public function testSuccessfulReservationCreation(): void {
        // ARRANGE
        $user = new User('u1', '[test@test.com](mailto:test@test.com)', 'hash', 'Doe', 'John');
        $parking = new Parking('p1', 48.85, 2.35, 10, [], ['24/7' => true]);

        $this->userRepo->method('findById')->willReturn($user);
        $this->parkingRepo->method('findById')->willReturn($parking);
        $this->reservationRepo->method('findActiveByParking')->willReturn([]);

        $input = new CreateReservationInput(
            userId: 'u1',
            parkingId: 'p1',
            debut: time(),
            fin: time() + 3600
        );

        // ACT
        $output = $this->useCase->execute($input);

        // ASSERT
        $this->assertNotNull($output->id);
        $this->assertEquals('active', $output->statut);
    }

    public function testThrowsExceptionWhenParkingFull(): void {
        // ARRANGE
        $user = new User('u1', '[test@test.com](mailto:test@test.com)', 'hash', 'Doe', 'John');
        $parking = new Parking('p1', 48.85, 2.35, 2, [], ['24/7' => true]);

        $this->userRepo->method('findById')->willReturn($user);
        $this->parkingRepo->method('findById')->willReturn($parking);

        // Simuler parking plein (2 réservations pour 2 places)
        $this->reservationRepo->method('findActiveByParking')
            ->willReturn([new Reservation(), new Reservation()]);

        $input = new CreateReservationInput(
            userId: 'u1',
            parkingId: 'p1',
            debut: time(),
            fin: time() + 3600
        );

        // ACT & ASSERT
        $this->expectException(ParkingFullException::class);
        $this->useCase->execute($input);
    }
}
```

### Test Fonctionnel (End-to-End)

```php
<?php
namespace Tests\Functional;

use PHPUnit\Framework\TestCase;

class UserReservationFlowTest extends TestCase {
    private $pdo;

    protected function setUp(): void {
        // Setup BDD test
        $this->pdo = new PDO('sqlite::memory:');
        $this->createTables();
    }

    public function testCompleteUserReservationFlow(): void {
        // 1. Inscription User
        $response = $this->post('/api/auth/register/user', [
            'email' => '[john@test.com](mailto:john@test.com)',
            'password' => 'Password123',
            'nom' => 'Doe',
            'prenom' => 'John'
        ]);
        $this->assertEquals(201, $response['status']);

        // 2. Connexion
        $response = $this->post('/api/auth/login', [
            'email' => '[john@test.com](mailto:john@test.com)',
            'password' => 'Password123'
        ]);
        $this->assertEquals(200, $response['status']);
        $token = $response['data']['token'];

        // 3. Recherche parkings
        $response = $this->get('/api/parkings/search?lat=48.8566&lng=2.3522', [
            'Authorization' => 'Bearer ' . $token
        ]);
        $this->assertEquals(200, $response['status']);
        $this->assertNotEmpty($response['data']['parkings']);

        // 4. Réservation
        $parkingId = $response['data']['parkings'][0]['id'];
        $response = $this->post('/api/reservations', [
            'parking_id' => $parkingId,
            'debut' => time() + 3600,
            'fin' => time() + 7200
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);
        $this->assertEquals(201, $response['status']);
        $this->assertArrayHasKey('reservation', $response['data']);
    }

    private function post(string $url, array $data, array $headers = []): array {
        // Helper pour simuler requête POST
    }
}
```

---

## 🛡️ Sécurité - Checklist

### Avant Chaque Commit

```php
// ✅ Vérifier : Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

// ❌ JAMAIS de concaténation
$query = "SELECT * FROM users WHERE email = '" . $email . "'";

// ✅ Vérifier : Hashage bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// ❌ JAMAIS de MD5 ou SHA1
$hash = md5($password); // VULNÉRABLE

// ✅ Vérifier : Escape HTML output
echo htmlspecialchars($user->getNom(), ENT_QUOTES, 'UTF-8');

// ❌ JAMAIS d'output direct
echo $user->getNom(); // XSS

// ✅ Vérifier : Validation JWT
if (!$this->jwtService->validate($token)) {
    throw new UnauthorizedException();
}
```

---

## 📊 Exécuter les Tests

```bash
# Tous les tests
vendor/bin/phpunit

# Tests Domain uniquement
vendor/bin/phpunit --testsuite Domain

# Tests avec couverture
vendor/bin/phpunit --coverage-html coverage/

# Ouvrir rapport couverture
open coverage/index.html

# Vérifier couverture minimale
vendor/bin/phpunit --coverage-text --coverage-filter src/Domain
vendor/bin/phpunit --coverage-text --coverage-filter src/Application

# Test spécifique
vendor/bin/phpunit tests/Domain/Entities/UserTest.php
```

---

## 🐛 Débogage

### Logs

```php
// Option 1 : error_log natif PHP
error_log("Debug: " . print_r($variable, true));

// Option 2 : var_dump avec buffer
ob_start();
var_dump($variable);
$debug = ob_get_clean();
error_log($debug);

// Option 3 : Exceptions avec contexte
throw new RuntimeException(
    "Erreur lors de la réservation",
    0,
    previous: $originalException
);
```

### Xdebug (Optionnel)

```
; php.ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=yes
```

---

## ✅ Checklist Avant Merge

### Code

-   [ ] Conventions camelCase respectées
-   [ ] Fonctions < 20 lignes
-   [ ] PHPDoc sur fonctions publiques
-   [ ] Pas de SQL dans Use Cases
-   [ ] Pas de logique métier dans Controllers
-   [ ] Repository interfaces dans Domain

### Tests

-   [ ] Tests unitaires écrits
-   [ ] Tests passent (vendor/bin/phpunit)
-   [ ] Couverture >= 60% Use Cases
-   [ ] Couverture >= 80% Domain

### Sécurité

-   [ ] Prepared statements utilisés
-   [ ] Mots de passe hashés avec bcrypt
-   [ ] Output HTML échappé (htmlspecialchars)
-   [ ] JWT validé sur routes protégées

### Git

-   [ ] Message commit clair
-   [ ] Pas de fichiers sensibles (.env, config locaux)
-   [ ] Branch à jour avec develop

---

## 📚 Ressources Utiles

### Documentation Officielle

-   PHP 8.x : https://www.php.net/manual/fr/
-   PHPUnit : https://phpunit.de/documentation.html
-   JWT : https://jwt.io/

### Outils

-   Postman : Tester API REST
-   TablePlus / DBeaver : GUI base de données
-   PHPStorm / VSCode : IDE

### Commandes Rapides

```bash
# Lancer serveur PHP local
php -S [localhost:8000](http://localhost:8000) -t public/

# Vérifier syntaxe PHP
php -l src/Domain/Entities/User.php

# Autoload Composer
composer dump-autoload

# Installer dépendances
composer install

# Mettre à jour dépendances
composer update
```

---

## 👥 Répartition Travail (Équipe 4)

### Semaine 1-2 : Fondations

-   **Personne 1** : Domain Entities + Interfaces
-   **Personne 2** : Repositories SQL
-   **Personne 3** : Repositories File + JWT
-   **Personne 4** : Setup projet + Config

### Semaine 3-4 : Use Cases

-   **Personne 1** : Use Cases Auth + Owner
-   **Personne 2** : Use Cases User + Recherche
-   **Personne 3** : Use Cases Stationnement
-   **Personne 4** : DTOs + Validators

### Semaine 5 : API

-   **Personne 1** : OwnerApiController
-   **Personne 2** : UserApiController
-   **Personne 3** : Middleware Auth
-   **Personne 4** : Router + Tests API

### Semaine 6 : Finalisation

-   **Tous** : Tests + Documentation + QA

---

**Bon développement ! 🚀**
