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
    $realName = trim((string)Utils::input('real_name', ''));
    $email    = trim((string)Utils::input('email', ''));
    $groupId  = (int)Utils::input('group_id', 0);
    $status   = (int)Utils::input('status', 1);

    if ($id <= 0) {
        Utils::error('无效的ID');
    }
    if (!in_array($status, array(1, 2))) {
        Utils::error('无效的状态');
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
        Utils::error('无权编辑超级管理员');
    }

    $updateData = array(
        'real_name' => $realName ?: null,
        'email'     => $email ?: null,
        'group_id'  => $groupId > 0 ? $groupId : null,
        'status'    => $status,
    );

    $affected = $db->update(
        'admins',
        $updateData,
        '`id` = :where_id',
        array(':where_id' => $id)
    );

    AdminLog::record('admin', 'update', '编辑管理员', array(
        'id' => $id,
        'username' => $record['username'],
        'changes' => $updateData,
    ));

    Utils::success('更新成功', array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('更新失败: ' . $e->getMessage());
}
