<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;
use App\Model\Repository\IFranchiseRepository;

class HomeController {
    public function __construct(
        private IFranchiseRepository $franchises
    ) {}

    public function index(array $vars) : void {
        $franchises = $this->franchises->findAll();

        View::render('home/index', [
            'title' => 'Home',
            'franchises' => $franchises,
            'css' => ['home.css'],
            'js' => ['home.js']
        ]);
    }
}