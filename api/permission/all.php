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
    $permissions = Auth::getPermissionGroups();
    Utils::success('获取成功', $permissions);
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
