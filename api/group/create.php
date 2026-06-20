<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('group:create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $name        = trim((string)Utils::input('name', ''));
    $description = trim((string)Utils::input('description', ''));
    $permissions = Utils::input('permissions', array());

    if (empty($name)) {
        Utils::error('请输入分组名称');
    }
    if (strlen($name) > 64) {
        Utils::error('分组名称不能超过64个字符');
    }
    if (!is_array($permissions)) {
        $permissions = array();
    }

    $db = Database::getInstance();

    $exists = $db->fetchOne(
        'SELECT id FROM `admin_groups` WHERE `name` = :name AND `is_deleted` = 0 LIMIT 1',
        array(':name' => $name)
    );
    if ($exists) {
        Utils::error('分组名称已存在');
    }

    $permsJson = json_encode($permissions, JSON_UNESCAPED_UNICODE);

    $insertId = $db->insert('admin_groups', array(
        'name'        => $name,
        'description' => $description ?: null,
        'permissions' => $permsJson,
    ));

    AdminLog::record('group', 'create', '新增管理员分组', array(
        'id' => $insertId,
        'name' => $name,
    ));

    Utils::success('添加成功', array('id' => $insertId));
} catch (Exception $e) {
    Utils::error('添加失败: ' . $e->getMessage());
}
