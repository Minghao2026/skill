<?php
/**
 * 管理员模型
 */
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class AdminModel
{
    public static function findByUsername(string $username): ?array
    {
        $rows = Database::select(
            "SELECT * FROM swt_admin WHERE username = ? AND deleted_at IS NULL LIMIT 1",
            [$username]
        );
        return $rows[0] ?? null;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        return Database::execute(
            "UPDATE swt_admin SET password = ? WHERE id = ? AND deleted_at IS NULL",
            [$hash, $id]
        ) > 0;
    }

    public static function ensureExists(string $username, string $passwordHash): void
    {
        $existing = self::findByUsername($username);
        if ($existing === null) {
            Database::insert(
                "INSERT INTO swt_admin (username, password, nickname, role) VALUES (?, ?, ?, ?)",
                [$username, $passwordHash, '超级管理员', 'admin']
            );
        }
    }
}
