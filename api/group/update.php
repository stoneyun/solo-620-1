<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('group:edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $id          = (int)Utils::input('id', 0);
    $name        = trim((string)Utils::input('name', ''));
    $description = trim((string)Utils::input('description', ''));
    $permissions = Utils::input('permissions', array());

    if ($id <= 0) {
        Utils::error('无效的ID');
    }
    if (empty($name)) {
        Utils::error('请输入分组名称');
    }
    if (!is_array($permissions)) {
        $permissions = array();
    }

    $db = Database::getInstance();

    $record = $db->fetchOne(
        'SELECT * FROM `admin_groups` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1',
        array(':id' => $id)
    );
    if (!$record) {
        Utils::error('记录不存在或已删除');
    }

    $exists = $db->fetchOne(
        'SELECT id FROM `admin_groups` WHERE `name` = :name AND `id` != :id AND `is_deleted` = 0 LIMIT 1',
        array(':name' => $name, ':id' => $id)
    );
    if ($exists) {
        Utils::error('分组名称已存在');
    }

    $permsJson = json_encode($permissions, JSON_UNESCAPED_UNICODE);

    $updateData = array(
        'name'        => $name,
        'description' => $description ?: null,
        'permissions' => $permsJson,
    );

    $affected = $db->update(
        'admin_groups',
        $updateData,
        '`id` = :where_id',
        array(':where_id' => $id)
    );

    AdminLog::record('group', 'update', '编辑管理员分组', array(
        'id' => $id,
        'name' => $name,
    ));

    Utils::success('更新成功', array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('更新失败: ' . $e->getMessage());
}
