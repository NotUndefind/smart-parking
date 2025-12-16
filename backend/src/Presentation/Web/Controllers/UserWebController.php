<?php

declare(strict_types=1);

namespace App\Presentation\Web\Controllers;

use App\Application\DTOs\Input\SearchParkingsByLocationInput;
use App\Application\UseCases\User\ListUserReservationsUseCase;
use App\Application\UseCases\User\SearchParkingsByLocationUseCase;

final class UserWebController
{
    public function __construct(
        private SearchParkingsByLocationUseCase $searchParkingsByLocationUseCase,
        private ListUserReservationsUseCase $listUserReservationsUseCase
    ) {
    }

    /**
     * Page d'accueil utilisateur (formulaire de recherche + résultats éventuels).
     */
    public function home(): void
    {
        $latitude = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
        $longitude = isset($_GET['lng']) ? (float) $_GET['lng'] : null;
        $radius = isset($_GET['radius']) ? (float) $_GET['radius'] : 5.0;

        $error = null;
        $results = [];

        if ($latitude !== null && $longitude !== null) {
            try {
                $input = new SearchParkingsByLocationInput(
                    latitude: $latitude,
                    longitude: $longitude,
                    radiusKm: $radius
                );

                $results = $this->searchParkingsByLocationUseCase->execute($input);
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (\Throwable $e) {
                $error = 'Une erreur est survenue lors de la recherche des parkings.';
            }
        }

        $this->renderHome($latitude, $longitude, $radius, $results, $error);
    }

    /**
     * Page d'historique des réservations utilisateur.
     * Dans un vrai contexte, l'id utilisateur viendrait de la session / JWT.
     */
    public function reservations(): void
    {
        $userId = $_GET['user_id'] ?? null;
        if ($userId === null) {
            echo '<h1>Historique des réservations</h1>';
            echo '<p>Paramètre <code>user_id</code> manquant.</p>';
            return;
        }

        try {
            $reservations = $this->listUserReservationsUseCase->execute((string) $userId);
        } catch (\Throwable $e) {
            echo '<h1>Historique des réservations</h1>';
            echo '<p>Impossible de charger les réservations pour cet utilisateur.</p>';
            return;
        }

        $this->renderReservations($reservations, (string) $userId);
    }

    /**
     * Petit rendu HTML inline pour la page d'accueil.
     *
     * @param float|null $latitude
     * @param float|null $longitude
     * @param float      $radius
     * @param array      $results  Tableau de ParkingOutput
     * @param string|null $error
     */
    private function renderHome(
        ?float $latitude,
        ?float $longitude,
        float $radius,
        array $results,
        ?string $error
    ): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Smart Parking - Recherche</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 2rem; }
                form { margin-bottom: 2rem; }
                .error { color: red; margin-bottom: 1rem; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: left; }
                th { background-color: #f5f5f5; }
            </style>
        </head>
        <body>
        <h1>Recherche de parkings</h1>

        <?php if ($error !== null): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="get">
            <label>
                Latitude :
                <input type="number" step="0.000001" name="lat"
                       value="<?= $latitude !== null ? htmlspecialchars((string) $latitude, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
            </label>
            <br><br>
            <label>
                Longitude :
                <input type="number" step="0.000001" name="lng"
                       value="<?= $longitude !== null ? htmlspecialchars((string) $longitude, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
            </label>
            <br><br>
            <label>
                Rayon (km) :
                <input type="number" step="0.1" name="radius"
                       value="<?= htmlspecialchars((string) $radius, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            </label>
            <br><br>
            <button type="submit">Rechercher</button>
        </form>

        <?php if (!empty($results)): ?>
            <h2>Parkings trouvés</h2>
            <table>
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Places totales</th>
                    <th>Distance (km)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $parkingOutput): ?>
                    <tr>
                        <td><?= htmlspecialchars($parkingOutput->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($parkingOutput->address, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= (int) $parkingOutput->totalSpots ?></td>
                        <td><?= $parkingOutput->distanceKm !== null ? number_format($parkingOutput->distanceKm, 2, ',', ' ') : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        </body>
        </html>
        <?php
    }

    /**
     * Rendu simple de la liste des réservations.
     *
     * @param array  $reservations Tableau de ReservationOutput
     * @param string $userId
     */
    private function renderReservations(array $reservations, string $userId): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Smart Parking - Réservations utilisateur</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 2rem; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ccc; padding: 0.5rem; text-align: left; }
                th { background-color: #f5f5f5; }
            </style>
        </head>
        <body>
        <h1>Réservations de l'utilisateur <?= htmlspecialchars($userId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>

        <?php if (empty($reservations)): ?>
            <p>Aucune réservation trouvée.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Parking</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Prix estimé (€)</th>
                    <th>Statut</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation->parkingName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= (int) $reservation->startTime ?></td>
                        <td><?= (int) $reservation->endTime ?></td>
                        <td><?= number_format($reservation->estimatedPrice, 2, ',', ' ') ?></td>
                        <td><?= htmlspecialchars($reservation->status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        </body>
        </html>
        <?php
    }
}


