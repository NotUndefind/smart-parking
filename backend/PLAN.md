## Module Auth & Utilisateur – Plan d’implémentation

Ce document suit l’avancement du module d’authentification et de gestion utilisateur, en respectant la Clean Architecture.

### Phases

- **PHASE 1 – Setup & Infrastructure de base**
  - [x] Vérifier / adapter `composer.json` pour namespace `App\`
  - [x] Vérifier / adapter `phpunit.xml`
  - [x] Vérifier / créer l’arborescence `src/Domain`, `src/Application`, `src/Infrastructure`, `src/Presentation`
  - [x] Initialiser `public/index.php` (point d’entrée)
  - [x] Créer / compléter `config/database.php` (PDO)
  - [x] Créer / compléter `config/jwt.php`
  - [x] Créer un router API simple (GET/POST)
  - [ ] Créer `.env.example`

- **PHASE 2 – Domain & Sécurité**
  - [ ] Entité `User` (Domain)
  - [ ] Exceptions `InvalidCredentialsException`, `UserNotFoundException`, `UnauthorizedAccessException`
  - [ ] Interface `UserRepositoryInterface`
  - [ ] Service `PasswordHasher`
  - [ ] Service `JWTService`
  - [ ] Repositories `MySQLUserRepository`, `FileUserRepository`

- **PHASE 3 – Application Layer (Use Cases)**
  - [ ] DTOs `RegisterUserInput`, `LoginInput`, `UserOutput`, `AuthTokenOutput`
  - [ ] Validators `EmailValidator`, `PasswordValidator`
  - [ ] Use cases `RegisterUserUseCase`, `LoginUseCase`

- **PHASE 4 – Presentation & Routing**
  - [ ] `AuthMiddleware`
  - [ ] `UserApiController`
  - [ ] Routes `POST /api/auth/register`, `POST /api/auth/login`, `GET /api/me`

- **PHASE 5 – Tests**
  - [ ] Tests unitaires `User`, `PasswordHasher`, `JWTService`
  - [ ] Tests d’intégration `RegisterUserUseCase`, `LoginUseCase`


