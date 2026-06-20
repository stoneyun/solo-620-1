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
    $username = trim((string)Utils::input('username', ''));
    $password = (string)Utils::input('password', '');

    $result = Auth::login($username, $password);

    if ($result === true) {
        $user = Auth::user();

        AdminLog::record('auth', 'login', '管理员登录', array(
            'id' => $user['id'],
            'username' => $user['username'],
        ));

        Utils::success('登录成功', array(
            'id'       => $user['id'],
            'username' => $user['username'],
            'real_name'=> $user['real_name'],
            'is_super' => $user['is_super'],
        ));
    } else {
        AdminLog::record('auth', 'login_fail', '登录失败', array(
            'username' => $username,
            'reason' => $result,
        ));
        Utils::error($result);
    }
} catch (Exception $e) {
    Utils::error('登录失败: ' . $e->getMessage());
}
