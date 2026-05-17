<?php declare(strict_types = 1);

namespace App\Exception;

class AuthException extends \RuntimeException {
    public const INVALID_CREDENTIALS = 101;
    public const EMAIL_NOT_VERIFIED = 102;
    public const USERNAME_ALREADY_TAKEN = 103;
    public const EMAIL_ALREADY_TAKEN = 104;
    public const INVALID_TOKEN = 105;
    public const EXPIRED_TOKEN = 106;
    public const DUPLICATE_ENTRY = 107;

    public static function invalidCredentials() : self {
        return new self('Invalid credentials.', self::INVALID_CREDENTIALS);
    }

    public static function emailNotVerified() : self {
        return new self('Email is not verified.', self::EMAIL_NOT_VERIFIED);
    }

    public static function usernameAlreadyTaken() : self {
        return new self('Username is already in use.', self::USERNAME_ALREADY_TAKEN);
    }

    public static function emailAlreadyTaken() : self {
        return new self('Email is already in use.', self::EMAIL_ALREADY_TAKEN);
    }

    public static function invalidToken() : self {
        return new self('Invalid Token', self::INVALID_TOKEN);
    }

    public static function expiredToken() : self {
        return new self('Token is expired', self::EXPIRED_TOKEN);
    }

    public static function duplicateEntry() : self {
        return new self('Entry already exists', self::DUPLICATE_ENTRY);
    }
}