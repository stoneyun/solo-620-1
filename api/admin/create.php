<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('admin:create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $username = trim((string)Utils::input('username', ''));
    $password = (string)Utils::input('password', '');
    $realName = trim((string)Utils::input('real_name', ''));
    $email = trim((string)Utils::input('email', ''));
    $groupId = (int)Utils::input('group_id', 0);
    $status = (int)Utils::input('status', 1);

    if (empty($username)) {
        Utils::error('请输入用户名');
    }
    if (strlen($username) < 3 || strlen($username) > 32) {
        Utils::error('用户名长度需3-32个字符');
    }
    if (empty($password)) {
        Utils::error('请输入密码');
    }
    if (strlen($password) < 6) {
        Utils::error('密码长度不能少于6位');
    }
    if (!in_array($status, array(1, 2))) {
        Utils::error('无效的状态');
    }

    $db = Database::getInstance();

    $exists = $db->fetchOne(
        'SELECT id FROM `admins` WHERE `username` = :username AND `is_deleted` = 0 LIMIT 1',
        array(':username' => $username)
    );
    if ($exists) {
        Utils::error('用户名已存在');
    }

    $hashedPassword = Auth::hashPassword($password);

    $insertId = $db->insert('admins', array(
        'username'  => $username,
        'password'  => $hashedPassword,
        'real_name' => $realName ?: null,
        'email'     => $email ?: null,
        'group_id'  => $groupId > 0 ? $groupId : null,
        'is_super'  => 0,
        'status'    => $status,
    ));

    AdminLog::record('admin', 'create', '新增管理员', array(
        'id' => $insertId,
        'username' => $username,
    ));

    Utils::success('添加成功', array('id' => $insertId));
} catch (Exception $e) {
    Utils::error('添加失败: ' . $e->getMessage());
}
