# Smart Parkings

Projet fil rouge pour structurer une plateforme de gestion de parkings connectés. Le dépôt suit une Clean Architecture stricte pour séparer la logique métier des détails techniques et faciliter les contributions en équipe.

## Architecture

- `Domain` définit les entités, les règles métiers et les interfaces de repository. Aucun accès technique direct.
- `Application` orchestre les cas d’usage via des services (`UseCases/`), manipule des DTOs et valide les entrées/sorties.
- `Infrastructure` implémente les contrats du domaine (SQL, fichiers, services externes, sécurité, etc.).
- `Presentation` regroupe les contrôleurs API et Web, le middleware et les vues Blade-like.
- `public/` sert de front controller (ex: `php -S localhost:8000 -t public`).

Des explications détaillées sont disponibles dans `aiRule/Architecture.md`, `aiRule/Development.md` et `aiRule/GlobalContext.md`.

## Répartition des dossiers principaux

```
src/
├── Domain/            # Entités, exceptions, interfaces de repository
├── Application/       # Use cases, DTOs, validators
├── Infrastructure/    # Implémentations (SQL, fichiers, services techniques)
└── Presentation/      # API controllers, middleware, vues
```

Autres emplacements utiles :

- `config/` : fichiers de configuration applicative.
- `data/` : jeux de données ou fixtures.
- `public/` : point d’entrée HTTP.
- `tests/` : suites PHPUnit.
- `aiRule/` : documentation projet et règles d’architecture.

## Pré-requis

- PHP 8.2+ (CLI) avec `ext-json`.
- Composer 2.x.

## Installation locale

```bash
composer install
```

## Lancer les tests

```bash
./vendor/bin/phpunit
```

## Bonnes pratiques pour contribuer

- Respecter la séparation des couches (dépendances vers l’intérieur uniquement).
- Créer ou mettre à jour les DTOs et validateurs lors de nouveaux use cases.
- Implémenter les interfaces du domaine dans `Infrastructure` et injecter ces implémentations via les contrôleurs ou le bootstrap.
- Documenter les décisions techniques supplémentaires dans `aiRule/`.

## Aller plus loin

- `aiRule/Architecture.md` : détail complet des couches.
- `aiRule/Development.md` : conventions de code, workflow Git, checklist qualité.
- `aiRule/GlobalContext.md` : contexte métier et objectifs fonctionnels.

Ces documents servent de référence pour garder le projet aligné avec les attentes pédagogiques.
