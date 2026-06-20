<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('admin:view');

try {
    $page     = max(1, (int)Utils::input('page', 1));
    $pageSize = max(1, min(100, (int)Utils::input('page_size', 10)));
    $keyword  = trim((string)Utils::input('keyword', ''));
    $status   = (int)Utils::input('status', 0);
    $groupId  = (int)Utils::input('group_id', 0);

    $db    = Database::getInstance();
    $where = array('a.`is_deleted` = 0');
    $params = array();

    if ($keyword !== '') {
        $where[] = '(a.`username` LIKE :keyword OR a.`real_name` LIKE :keyword)';
        $params[':keyword'] = '%' . $keyword . '%';
    }

    if ($status > 0) {
        $where[] = 'a.`status` = :status';
        $params[':status'] = $status;
    }

    if ($groupId > 0) {
        $where[] = 'a.`group_id` = :group_id';
        $params[':group_id'] = $groupId;
    }

    $whereSql = implode(' AND ', $where);

    $totalSql = 'SELECT COUNT(*) AS total FROM `admins` a WHERE ' . $whereSql;
    $totalRow = $db->fetchOne($totalSql, $params);
    $total    = (int)$totalRow['total'];

    $offset = ($page - 1) * $pageSize;
    $listSql = sprintf(
        'SELECT a.`id`, a.`username`, a.`real_name`, a.`email`, a.`group_id`, a.`is_super`, a.`status`, 
                a.`last_login_ip`, a.`last_login_at`, a.`login_count`, a.`created_at`,
                g.`name` AS group_name
         FROM `admins` a
         LEFT JOIN `admin_groups` g ON a.`group_id` = g.`id` AND g.`is_deleted` = 0
         WHERE %s 
         ORDER BY a.`id` DESC 
         LIMIT %d, %d',
        $whereSql,
        $offset,
        $pageSize
    );

    $list = $db->fetchAll($listSql, $params);

    Utils::success('获取成功', array(
        'total'     => $total,
        'page'      => $page,
        'page_size' => $pageSize,
        'list'      => $list,
    ));
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
