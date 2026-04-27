<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;
use App\Model\Repository\IFranchiseRepository;

class HomeController extends BaseController {
    public function __construct(
        private IFranchiseRepository $franchises
    ) {}

    public function index(array $vars) : void {
        $franchises = $this->franchises->findAll();

        $this->render('home/index', [
            'title' => 'Home | DLE Games Daily',
            'franchises' => $franchises,
            'css' => ['home.css'],
            'js' => ['home.js']
        ]);
    }
}