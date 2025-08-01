<?php
require_once 'Singleton_database.php';

// header for api control
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}


// API-Endpoint logic
header('Content-Type: application/json');

$pflanzen = new Pflanzen();
$method = $_SERVER['REQUEST_METHOD'];

// Data from input into json decode
$input = json_decode(file_get_contents('php://input'), true);

// proper response
$response = ['success' => false, 'message' => 'Ungültige Anfrage', 'data' => null];

try {
    switch ($method) {
        case 'GET':
            $data = $pflanzen->read();
            $response = [
                'success' => true,
                'message' => count($data) . ' Pflanzen gefunden',
                'data' => $data
            ];
            break;

        case 'POST':
            if (isset($input['name'], $input['kaufdatum'], $input['standort'], $input['bewaesserung_in_tage'])) {
                $result = $pflanzen->create(
                    $input['name'],
                    $input['kaufdatum'],
                    $input['standort'],
                    (int)$input['bewaesserung_in_tage'],
                    $input['gegossen'] ?? null
                );

                $response = [
                    'success' => $result,
                    'message' => $result ? 'Pflanze erfolgreich erstellt' : 'Fehler beim Erstellen der Pflanze',
                    'data' => null
                ];
            } else {
                $response['message'] = 'Fehlende erforderliche Felder';
            }
            break;

        case 'PUT':
            if (isset($input['id'])) {
                $id = (int)$input['id'];
                unset($input['id']); // ID aus den zu aktualisierenden Daten entfernen

                $result = $pflanzen->update($id, $input);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Pflanze erfolgreich aktualisiert' : 'Fehler beim Aktualisieren oder keine Änderungen',
                    'data' => null
                ];
            } else {
                $response['message'] = 'Fehlende Pflanzen-ID';
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $result = $pflanzen->delete($id);

                $response = [
                    'success' => $result,
                    'message' => $result ? 'Pflanze erfolgreich gelöscht' : 'Pflanze nicht gefunden oder Fehler beim Löschen',
                    'data' => null
                ];
            } else {
                $response['message'] = 'Fehlende Pflanzen-ID';
            }
            break;

        default:
            $response['message'] = 'Methode nicht unterstützt';
            break;
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Serverfehler: ' . $e->getMessage(),
        'data' => null
    ];
}

echo json_encode($response);

