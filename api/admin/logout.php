<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $userId = Auth::id();
    $username = Auth::username();

    Auth::logout();

    if ($userId > 0) {
        AdminLog::record('auth', 'logout', '管理员登出', array(
            'id' => $userId,
            'username' => $username,
        ));
    }

    Utils::success('已退出登录');
} catch (Exception $e) {
    Utils::error('操作失败: ' . $e->getMessage());
}
