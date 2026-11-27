<?php declare(strict_types = 1);

namespace App\Service;

final class UserInputValidator {
    private const USERNAME_PATTERN = '/^[a-zA-Z0-9_]{3,20}\z/';
    private const EMAIL_MAX_LENGTH = 150;
    private const PASSWORD_MIN_LENGTH = 8;
    private const PASSWORD_MAX_LENGTH = 72;         // bcrypt limit

    public static function username(string $username) : ?string {
        return preg_match(self::USERNAME_PATTERN, $username) === 1
            ? null
            : 'Username must be 3-20 characters (alphanumerical and underscore)';
    }

    public static function email(string $email) : ?string {
        return strlen($email) <= self::EMAIL_MAX_LENGTH && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            ? null
            : 'Please provide a valid email address.';
    }

    public static function password(string $password) : ?string {
        $length = strlen($password);

        if($length < self::PASSWORD_MIN_LENGTH) {
            return 'Password must be at least' . self::PASSWORD_MIN_LENGTH . ' characters.';
        }

        if($length > self::PASSWORD_MAX_LENGTH) {
            return 'Password must be at most ' . self::PASSWORD_MAX_LENGTH . ' characters.';
        }

        return null;
    }
}