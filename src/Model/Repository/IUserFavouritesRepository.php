<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface IUserFavouritesRepository {
    public function add(int $userId, int $franchiseId) : void;
    public function remove(int $userId, int $franchiseId) : void;
    public function isFavourite(int $userId, int $franchiseId) : bool;
    public function findFranchiseIdsByUser(int $userId) : array;
}