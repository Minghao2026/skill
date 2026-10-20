<?php
/**
 * 图标辅助: 将内容表中的 icon 名称映射为 emoji, 配合 CSS 彩色徽章渲染
 * 后端可自由配置 icon 字段值 (见下方映射表)
 */
declare(strict_types=1);

if (!function_exists('swt_icon')) {
    function swt_icon(string $name): string
    {
        static $map = [
            'rocket' => '🚀', 'money' => '💰', 'zero' => '🆓', 'ai' => '🤖', 'private' => '🔒', 'handshake' => '🤝',
            'doc' => '📝', 'build' => '🛠️', 'run' => '🏃', 'smart' => '✨',
            'food' => '🍜', 'shop' => '🛍️', 'beauty' => '💇', 'fun' => '🎮', 'life' => '🔧', 'travel' => '🎡', 'hotel' => '🏨', 'tea' => '🧋',
            'map' => '🗺️', 'check' => '✅', 'heart' => '❤️', 'pin' => '📍', 'chat' => '💬', 'group' => '👥', 'box' => '📦',
            'video' => '🎬', 'pen' => '✍️', 'target' => '🎯', 'chart' => '📊',
            'phone' => '📞', 'wechat' => '💬', 'star' => '⭐', 'service' => '🎧', 'shield' => '🛡️', 'bolt' => '⚡', 'gift' => '🎁', 'trophy' => '🏆', 'sparkle' => '✨', 'store' => '🏪',
            // 服务流程步骤（后台可配置的常见 icon 名称）
            'step' => '🔹', 'step1' => '①', 'step2' => '②', 'step3' => '③', 'step4' => '④', 'step5' => '⑤', 'step6' => '⑥',
            'search' => '🔍', 'phone_call' => '📞', 'cart' => '🛒', 'crown' => '👑', 'medal' => '🎖️', 'flag' => '🚩',
            'fire' => '🔥', 'rocket2' => '🧨', 'gear' => '⚙️', 'link' => '🔗', 'eye' => '👀', 'lock' => '🔐',
        ];
        // 兜底用 ● (U+25CF) 而非 🔹：菱形 emoji 在部分安卓/Windows 字体中缺字形，会渲染成方框
        return $map[$name] ?? '●';
    }
}

if (!function_exists('swt_section')) {
    /** 取板块标题/简介 */
    function swt_section(array $sections, string $key, string $field, string $default = ''): string
    {
        return $sections[$key][$field] ?? $default;
    }
}
