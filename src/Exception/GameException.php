<?php declare(strict_types = 1);

namespace App\Exception;

class GameException extends \RuntimeException {
    public const CHARACTER_NOT_FOUND = 201;
    public const ALREADY_GUESSED = 202;
    public const ALREADY_COMPLETED = 203;
    public const NO_IDENTITY = 204;

    public static function characterNotFound() : self {
        return new self('Character not found', self::CHARACTER_NOT_FOUND);
    }

    public static function alreadyGuessed() : self {
        return new self('Character already guessed', self::ALREADY_GUESSED);
    }

    public static function alreadyCompleted() : self {
        return new self('Game already completed', self::ALREADY_COMPLETED);
    }

    public static function noIdentity() : self {
        return new self('Session expired, please reload the page', self::NO_IDENTITY);
    }
}