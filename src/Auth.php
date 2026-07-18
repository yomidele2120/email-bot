<?php
namespace App;

class Auth
{
    public static function register(string $email, string $password, string $name): array
    {
        $pdo = Database::connect();

        $existing = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $existing->execute([':email' => $email]);
        if ($existing->fetch()) {
            return ['success' => false, 'error' => 'An account with this email already exists.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name) VALUES (:email, :hash, :name)");
        $stmt->execute([':email' => $email, ':hash' => $hash, ':name' => $name]);

        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        $_SESSION['user_name'] = $name;

        return ['success' => true];
    }

    public static function attempt(string $email, string $password): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Incorrect email or password.'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        return ['success' => true];
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function name(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
