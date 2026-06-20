<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('admin:edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $id       = (int)Utils::input('id', 0);
    $password = (string)Utils::input('password', '');

    if ($id <= 0) {
        Utils::error('无效的ID');
    }
    if (empty($password)) {
        Utils::error('请输入新密码');
    }
    if (strlen($password) < 6) {
        Utils::error('密码长度不能少于6位');
    }

    $db = Database::getInstance();

    $record = $db->fetchOne(
        'SELECT * FROM `admins` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1',
        array(':id' => $id)
    );
    if (!$record) {
        Utils::error('记录不存在或已删除');
    }

    if ($record['is_super'] && !Auth::isSuper()) {
        Utils::error('无权操作超级管理员');
    }

    $hashedPassword = Auth::hashPassword($password);
    $affected = $db->update(
        'admins',
        array('password' => $hashedPassword),
        '`id` = :where_id',
        array(':where_id' => $id)
    );

    AdminLog::record('admin', 'update', '重置管理员密码', array(
        'id' => $id,
        'username' => $record['username'],
    ));

    Utils::success('密码重置成功', array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('重置失败: ' . $e->getMessage());
}
