<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('admin:delete');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $id = (int)Utils::input('id', 0);

    if ($id <= 0) {
        Utils::error('无效的ID');
    }

    if ($id === Auth::id()) {
        Utils::error('不能删除自己');
    }

    $db = Database::getInstance();

    $record = $db->fetchOne(
        'SELECT * FROM `admins` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1',
        array(':id' => $id)
    );
    if (!$record) {
        Utils::error('记录不存在或已删除');
    }

    if ($record['is_super']) {
        Utils::error('不能删除超级管理员');
    }

    $affected = $db->update(
        'admins',
        array('is_deleted' => 1),
        '`id` = :where_id',
        array(':where_id' => $id)
    );

    AdminLog::record('admin', 'delete', '删除管理员', array(
        'id' => $id,
        'username' => $record['username'],
    ));

    Utils::success('删除成功', array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('删除失败: ' . $e->getMessage());
}
