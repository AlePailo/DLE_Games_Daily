<?php declare(strict_types = 1);

namespace App\Controller;

use App\View\View;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\ICharacterRepository;

class GameController extends BaseController{
    public function __construct(
        private IFranchiseRepository $franchise,
        private ICharacterRepository $characters
    ) {}

    public function index(array $vars) : void {
        $slug = $vars['slug'];
        $franchise = $this->franchise->findBySlug($slug);

        if(!$franchise) {
            $this->notFound();
        }

        $characters = $this->characters->findByFranchiseId($franchise->getId());

        $this->render("game/index", [
            'title' => "{$slug} | DLE Games Daily",
            'characters' => $characters,
            'css' => ['game.css'],
            'js' => ['game.js']
        ]);
    }
}