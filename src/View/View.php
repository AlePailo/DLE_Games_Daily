<?php declare(strict_types = 1);

namespace App\View;

class View {
    public static function render(string $template, array $data = []) : void {
        extract($data);
        
        ob_start();
        require BASE_PATH . "templates/{$template}.php";
        $content = ob_get_clean();
        
        require BASE_PATH . 'templates/layout/header.php';
        echo $content;
        //require BASE_PATH . 'templates/layout/footer.php';
    }
}