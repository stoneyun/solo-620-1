<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

if (!Auth::check()) {
    Utils::jsonResponse(401, '请先登录');
}

try {
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT `id`, `name` FROM `admin_groups` WHERE `is_deleted` = 0 ORDER BY `id` ASC'
    );

    Utils::success('获取成功', $list);
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
