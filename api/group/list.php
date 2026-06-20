<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('group:view');

try {
    $page     = max(1, (int)Utils::input('page', 1));
    $pageSize = max(1, min(100, (int)Utils::input('page_size', 20)));
    $keyword  = trim((string)Utils::input('keyword', ''));

    $db    = Database::getInstance();
    $where = array('`is_deleted` = 0');
    $params = array();

    if ($keyword !== '') {
        $where[] = '(`name` LIKE :keyword OR `description` LIKE :keyword)';
        $params[':keyword'] = '%' . $keyword . '%';
    }

    $whereSql = implode(' AND ', $where);

    $totalSql = 'SELECT COUNT(*) AS total FROM `admin_groups` WHERE ' . $whereSql;
    $totalRow = $db->fetchOne($totalSql, $params);
    $total    = (int)$totalRow['total'];

    $offset = ($page - 1) * $pageSize;
    $listSql = sprintf(
        'SELECT `id`, `name`, `description`, `permissions`, `created_at`, `updated_at` 
         FROM `admin_groups` 
         WHERE %s 
         ORDER BY `id` ASC 
         LIMIT %d, %d',
        $whereSql,
        $offset,
        $pageSize
    );

    $list = $db->fetchAll($listSql, $params);
    foreach ($list as &$item) {
        $perms = json_decode($item['permissions'], true);
        $item['permissions'] = is_array($perms) ? $perms : array();
        $item['perm_count'] = count($item['permissions']);
    }
    unset($item);

    Utils::success('获取成功', array(
        'total'     => $total,
        'page'      => $page,
        'page_size' => $pageSize,
        'list'      => $list,
    ));
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
