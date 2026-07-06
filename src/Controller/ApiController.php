<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;

class ApiController extends BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    protected function renderJson(mixed $data, int $status = 200) : void {
        if (ob_get_length()) { ob_clean(); }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        
        $json = json_encode($data, \JSON_UNESCAPED_UNICODE);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => 'Internal JSON Encoding Error: ' . json_last_error_msg()
            ]);
            exit;
        }
    
        echo $json;
        exit;
    }

    protected function getJsonBody() : array {
    // 1. Leggiamo il testo grezzo e puliamo eventuali spazi bianchi
        $rawInput = trim(file_get_contents('php://input'));
        
        // 2. Se è vuoto, evitiamo di darlo in pasto a json_decode e ritorniamo subito un array vuoto
        if (empty($rawInput)) {
            return [];
        }

        // 3. Decodifichiamo. Il secondo parametro 'true' costringe PHP a trasformarlo in array associativo
        $data = json_decode($rawInput, true);

        // 4. Cintura di sicurezza: se json_decode fallisce (ritorna null), restituiamo comunque un array
        return is_array($data) ? $data : [];
    }

    protected function respondWithError(string $message, int $status = 400) : void {
        $this->renderJson([
            'success' => false,
            'error' => $message
        ], $status);
    }
}