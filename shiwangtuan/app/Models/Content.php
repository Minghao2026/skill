<?php
/**
 * 内容模型: 各内容表的通用读写 (后端可灵活配置前端内容)
 * 所有 SQL 均使用 PDO 预处理, 列名来自内置白名单, 杜绝注入
 */
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

class Content
{
    /** 各表可写字段白名单 (不含 id/created_at/updated_at/deleted_at) */
    private const SCHEMA = [
        'swt_settings'  => ['setting_key', 'setting_value', 'setting_group', 'sort'],
        'swt_nav'       => ['title', 'url', 'target', 'sort', 'is_show'],
        'swt_banner'    => ['title', 'subtitle', 'image', 'button_text', 'button_url', 'sort', 'is_show'],
        'swt_section'   => ['section_key', 'title', 'subtitle', 'content', 'sort'],
        'swt_advantage' => ['icon', 'title', 'description', 'sort', 'is_show'],
        'swt_service'   => ['icon', 'title', 'description', 'points', 'sort', 'is_show'],
        'swt_industry'  => ['icon', 'name', 'description', 'sort', 'is_show'],
        'swt_stat'      => ['label', 'value', 'unit', 'icon', 'sort', 'is_show'],
        'swt_case'      => ['title', 'category', 'cover', 'summary', 'metrics', 'sort', 'is_show'],
        'swt_ai'        => ['icon', 'title', 'description', 'sort', 'is_show'],
        'swt_process'   => ['step', 'icon', 'title', 'description', 'sort', 'is_show'],
        'swt_plan'      => ['name', 'price', 'original_price', 'unit', 'features', 'is_hot', 'sort', 'is_show'],
        'swt_partner'   => ['name', 'url', 'logo', 'sort', 'is_show'],
        'swt_team'      => ['name', 'title', 'avatar', 'intro', 'sort', 'is_show'],
        'swt_message'   => ['name', 'phone', 'company', 'city', 'content', 'source', 'status'],
    ];

    private const INT_FIELDS = ['sort', 'is_show', 'is_hot', 'step', 'status'];

    private static function allow(string $table): array
    {
        if (!isset(self::SCHEMA[$table])) {
            throw new RuntimeException('未知内容表: ' . $table);
        }
        return self::SCHEMA[$table];
    }

    /** 返回全部受支持的内容表名 (供控制器白名单校验) */
    public static function tables(): array
    {
        return array_keys(self::SCHEMA);
    }

    /** 返回某表的可用写字段 (供控制器构建写入数据) */
    public static function columns(string $table): array
    {
        return self::SCHEMA[$table] ?? [];
    }

    /** 站点设置 key=>value */
    public static function settings(): array
    {
        $rows = Database::select(
            "SELECT setting_key, setting_value FROM swt_settings WHERE deleted_at IS NULL ORDER BY sort ASC, id ASC"
        );
        $out = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
        return $out;
    }

    public static function setting(string $key, string $default = ''): string
    {
        $rows = Database::select(
            "SELECT setting_value FROM swt_settings WHERE setting_key = ? AND deleted_at IS NULL LIMIT 1",
            [$key]
        );
        return $rows[0]['setting_value'] ?? $default;
    }

    /** 列表 (前端仅取展示项, 后台取全部未删除)
     * 注意: 并非所有内容表都含 sort / is_show 列 (如 swt_message 留言表),
     * 须依据各表字段白名单动态决定是否追加排序/过滤子句, 否则会触发
     * "Unknown column 'sort'" 导致列表接口 500。
     */
    public static function list(string $table, bool $onlyShow = true): array
    {
        self::allow($table);
        $cols = self::SCHEMA[$table] ?? [];
        $hasSort = in_array('sort', $cols, true);
        $hasShow = in_array('is_show', $cols, true);
        $sql = "SELECT * FROM `{$table}` WHERE deleted_at IS NULL";
        if ($onlyShow && $hasShow) {
            $sql .= " AND is_show = 1";
        }
        $sql .= $hasSort ? " ORDER BY sort ASC, id ASC" : " ORDER BY id DESC";
        return Database::select($sql);
    }

    public static function get(string $table, int $id): ?array
    {
        self::allow($table);
        $rows = Database::select("SELECT * FROM `{$table}` WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$id]);
        return $rows[0] ?? null;
    }

    /** 创建或更新, 返回 id */
    public static function save(string $table, array $data, ?int $id = null): int
    {
        $allowed = self::allow($table);
        $cols = [];
        $params = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, $allowed, true)) {
                continue;
            }
            if (in_array($k, self::INT_FIELDS, true)) {
                $v = (int) $v;
            }
            $cols[$k] = $v;
        }
        if (empty($cols)) {
            throw new RuntimeException('无可写入字段');
        }

        if ($id === null) {
            $colList = implode(', ', array_keys($cols));
            $ph = implode(', ', array_map(static fn($c) => ':' . $c, array_keys($cols)));
            $sql = "INSERT INTO `{$table}` ({$colList}) VALUES ({$ph})";
            return Database::insert($sql, $cols);
        }

        $set = implode(', ', array_map(static fn($c) => "`{$c}` = :{$c}", array_keys($cols)));
        $cols['__id'] = $id;
        $sql = "UPDATE `{$table}` SET {$set} WHERE id = :__id AND deleted_at IS NULL";
        Database::execute($sql, $cols);
        return $id;
    }

    /** 软删除 */
    public static function remove(string $table, int $id): bool
    {
        self::allow($table);
        $n = Database::execute(
            "UPDATE `{$table}` SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
        return $n > 0;
    }

    /** 前端首页所需全部内容聚合 */
    public static function siteBundle(): array
    {
        $sections = [];
        foreach (self::list('swt_section', false) as $s) {
            $sections[$s['section_key']] = $s;
        }
        return [
            'settings'  => self::settings(),
            'nav'       => self::list('swt_nav'),
            'banners'   => self::list('swt_banner'),
            'sections'  => $sections,
            'advantages' => self::list('swt_advantage'),
            'services'  => self::list('swt_service'),
            'industries' => self::list('swt_industry'),
            'stats'     => self::list('swt_stat'),
            'cases'     => self::list('swt_case'),
            'ai'        => self::list('swt_ai'),
            'process'   => self::list('swt_process'),
            'plans'     => self::list('swt_plan'),
            'partners'  => self::list('swt_partner'),
            'team'      => self::list('swt_team'),
        ];
    }

    /** 咨询留言入库 */
    public static function addMessage(array $data): int
    {
        return self::save('swt_message', $data);
    }

    public static function messages(int $status = -1, int $limit = 50): array
    {
        $sql = "SELECT * FROM swt_message WHERE deleted_at IS NULL";
        $params = [];
        if ($status >= 0) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY id DESC LIMIT ?";
        $params[] = $limit;
        return Database::select($sql, $params);
    }

    /** 各表记录数 (后台概览) */
    public static function counts(): array
    {
        $tables = ['swt_nav','swt_banner','swt_advantage','swt_service','swt_industry',
                   'swt_stat','swt_case','swt_ai','swt_process','swt_plan','swt_partner','swt_team','swt_message'];
        $out = [];
        foreach ($tables as $t) {
            $r = Database::select("SELECT COUNT(*) AS c FROM `{$t}` WHERE deleted_at IS NULL");
            $out[$t] = (int) ($r[0]['c'] ?? 0);
        }
        return $out;
    }
}
