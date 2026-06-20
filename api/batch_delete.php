<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $ids = Utils::input('ids', array());

    if (!is_array($ids) || empty($ids)) {
        Utils::error('请选择要删除的记录');
    }

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function ($v) {
        return $v > 0;
    });
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        Utils::error('无效的ID列表');
    }

    $db = Database::getInstance();

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = sprintf('UPDATE `invitation_codes` SET `is_deleted` = 1 WHERE `id` IN (%s) AND `is_deleted` = 0', $placeholders);

    $stmt = $db->query($sql, $ids);
    $affected = $stmt->rowCount();

    Utils::success("成功删除{$affected}条记录", array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('批量删除失败: ' . $e->getMessage());
}
