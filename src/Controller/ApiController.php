<?php declare(strict_types = 1);

namespace App\Controller;

class ApiController extends BaseController {

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
        $rawInput = trim(file_get_contents('php://input'));
        
        if (empty($rawInput)) {
            return [];
        }

        // If input is not empty transorm json in an associative array
        $data = json_decode($rawInput, true);

        // If decode fails (returns null), return an array anyway
        return is_array($data) ? $data : [];
    }

    protected function respondWithError(string $message, int $status = 400) : void {
        $this->renderJson([
            'success' => false,
            'error' => $message
        ], $status);
    }
}