<?php
/**
 * 数据库单例连接 (PDO)
 * 规范: DSN charset=utf8mb4; ERRMODE_EXCEPTION; EMULATE_PREPARES=false
 */
declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $cfg = $GLOBALS['DB_CONFIG'] ?? [];
            if (empty($cfg['dbname']) || empty($cfg['user'])) {
                throw new RuntimeException('数据库配置缺失');
            }
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'] ?? 3306,
                $cfg['dbname'],
                $cfg['charset'] ?? 'utf8mb4'
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES  => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => false,
            ];
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'] ?? '', $options);
        }
        return self::$instance;
    }

    /**
     * 统一执行查询并返回全部结果 (预处理防注入)
     */
    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 执行写操作, 返回受影响行数
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 插入并返回自增ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return (int) self::getConnection()->lastInsertId();
    }
}
