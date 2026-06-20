<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

if (!Auth::check()) {
    Utils::jsonResponse(401, '请先登录');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $oldPassword = (string)Utils::input('old_password', '');
    $newPassword = (string)Utils::input('new_password', '');
    $confirmPassword = (string)Utils::input('confirm_password', '');

    if (empty($oldPassword)) {
        Utils::error('请输入旧密码');
    }
    if (empty($newPassword)) {
        Utils::error('请输入新密码');
    }
    if (strlen($newPassword) < 6) {
        Utils::error('新密码长度不能少于6位');
    }
    if ($newPassword !== $confirmPassword) {
        Utils::error('两次输入的新密码不一致');
    }

    $db = Database::getInstance();
    $userId = Auth::id();

    $admin = $db->fetchOne(
        'SELECT `password` FROM `admins` WHERE `id` = :id LIMIT 1',
        array(':id' => $userId)
    );

    if (!$admin || !Auth::verifyPassword($oldPassword, $admin['password'])) {
        Utils::error('旧密码不正确');
    }

    $hashedPassword = Auth::hashPassword($newPassword);
    $db->update(
        'admins',
        array('password' => $hashedPassword),
        '`id` = :where_id',
        array(':where_id' => $userId)
    );

    AdminLog::record('admin', 'update', '修改自身密码', array('user_id' => $userId));

    Utils::success('密码修改成功，请重新登录');
} catch (Exception $e) {
    Utils::error('修改失败: ' . $e->getMessage());
}
