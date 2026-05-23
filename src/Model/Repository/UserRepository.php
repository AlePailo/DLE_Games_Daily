<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\User;
use App\Model\Entity\UserPassword;

class UserRepository implements IUserRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function findById(int $id) : ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        
        return $res ? $this->mapUser($res) : null;
    }

    public function findByUsername(string $username) : ?User {
        $stmt= $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $res = $stmt->fetch();

        return $res ? $this->mapUser($res) : null;
    }

    public function findByEmail(string $email) : ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $res = $stmt->fetch();
        
        return $res ? $this->mapUser($res) : null;
    }

    public function findUserPasswordByUserId(int $userId): ?UserPassword {
        $stmt = $this->pdo->prepare("SELECT * FROM users_passwords WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $res = $stmt->fetch();

        return $res ? $this->mapUserPassword($res) : null;
    }

    public function findUserPasswordByToken(string $token): ?UserPassword {
        $stmt = $this->pdo->prepare("SELECT * FROM users_passwords WHERE verification_token = :verification_token");
        $stmt->execute(['verification_token' => $token]);
        $res = $stmt->fetch();

        return $res ? $this->mapUserPassword($res) : null;
    }

    public function findByOAuthProvider(string $provider, string $provider_id): ?User {
        $stmt = $this->pdo->prepare("SELECT u.* FROM users u INNER JOIN users_oauth uo ON u.id = uo.user_id WHERE uo.provider = :provider AND uo.provider_id = :provider_id");
        $stmt->execute(['provider' => $provider, 'provider_id' => $provider_id]);
        $res = $stmt->fetch();
        
        return $res ? $this->mapUser($res) : null;
    }

    public function createLocalUser(string $username, string $email, string $passwordHash, string $token, \DateTimeImmutable $expiresAt) : int {
        try {
            $this->pdo->beginTransaction();
            
            $userId = $this->createUser($username, $email);
            $stmt = $this->pdo->prepare("INSERT INTO users_passwords (user_id, password_hash, verification_token, token_expires_at) VALUES (:user_id, :password_hash, :verification_token, :token_expires_at)");
            $stmt->execute(['user_id' => $userId, 'password_hash' => $passwordHash, 'verification_token' => $token, 'token_expires_at' => $expiresAt->format('Y-m-d H:i:s')]);

            $this->pdo->commit();
            return $userId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();    
            throw $e;
        }
    }

    public function createOAuthUser(string $username, string $email, string $provider, string $providerId) : int {
        try {
            $this->pdo->beginTransaction();

            $userId = $this->createUser($username, $email);
            $stmt = $this->pdo->prepare("INSERT INTO users_oauth (user_id, provider, provider_id) VALUES (:user_id, :provider, :provider_id)");
            $stmt->execute(['user_id' => $userId, 'provider' => $provider, 'provider_id' => $providerId]);

            $this->pdo->commit();
            return $userId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    private function createUser(string $username, string $email) : int {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email) VALUES (:username, :email)");
        $stmt->execute(['username' => $username, 'email' => $email]);
        return (int)$this->pdo->lastInsertId();
    }

    private function mapUser(array $row) : User {
        return new User(
            id: (int)$row['id'],
            username: $row['username'],
            email: $row['email'],
            isVerified: (bool)$row['is_verified'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
    }

    private function mapUserPassword(array $row) : UserPassword {
        return new UserPassword(
            userId: (int)$row['user_id'],
            passwordHash: $row['password_hash'],
            verificationToken: $row['verification_token'] ?? null,
            tokenExpiresAt: $row['token_expires_at'] ? new \DateTimeImmutable($row['token_expires_at']) : null
        );
    }

    public function markAsVerified(int $id) : void {
        $stmt = $this->pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function updateVerificationToken(int $userId, string $hashedToken, \DateTimeImmutable $expires) : void {
        $stmt = $this->pdo->prepare("UPDATE users_passwords SET verification_token = :verification_token, token_expires_at = :token_expires_at WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId, 'verification_token' => $hashedToken, 'token_expires_at' => $expires->format('Y-m-d H:i:s')]);
    }

    public function removeVerificationToken(int $userId) : void {
        $stmt = $this->pdo->prepare("UPDATE users_passwords SET verification_token = null, token_expires_at = null WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
    }

    public function deleteRememberToken(string $hashedToken) : void {
        $stmt = $this->pdo->prepare("DELETE FROM remember_tokens WHERE token_hash = :token_hash");
        $stmt->execute(['token_hash' => $hashedToken]);
    }

    public function saveRememberToken(int $userId, string $hash, \DateTimeImmutable $expires) : void {
        $stmt = $this->pdo->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)");
        $stmt->execute(['user_id' => $userId, 'token_hash' => $hash, 'expires_at' => $expires->format('Y-m-d H:i:s')]);
    }

    public function findByRememberToken(string $hashedToken) : ?User {
        $stmt = $this->pdo->prepare("SELECT u.* FROM users u INNER JOIN remember_tokens rt ON u.id = rt.user_id WHERE rt.token_hash = :token_hash AND rt.expires_at > NOW()");
        $stmt->execute(['token_hash' => $hashedToken]);
        $res = $stmt->fetch();

        return $res ? $this->mapUser($res) : null;
    }
}