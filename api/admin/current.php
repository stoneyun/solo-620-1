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
    $user = Auth::user();
    $db = Database::getInstance();

    $admin = $db->fetchOne(
        'SELECT `id`, `username`, `real_name`, `email`, `group_id`, `is_super`, `status`, `last_login_ip`, `last_login_at`, `login_count`, `created_at` 
         FROM `admins` WHERE `id` = :id LIMIT 1',
        array(':id' => $user['id'])
    );

    if ($admin) {
        $admin['is_super'] = (bool)$admin['is_super'];
        $admin['permissions'] = $user['permissions'];
        $admin['group_name'] = $user['group_name'];
    }

    Utils::success('获取成功', $admin);
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
