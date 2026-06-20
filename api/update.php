<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Utils.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/AdminLog.php';

Auth::requirePermission('invitation:edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $id       = (int)Utils::input('id', 0);
    $status   = (int)Utils::input('status', 0);
    $expireAt = trim((string)Utils::input('expire_at', ''));
    $usedBy   = trim((string)Utils::input('used_by', ''));
    $remark   = trim((string)Utils::input('remark', ''));

    if ($id <= 0) {
        Utils::error('无效的ID');
    }
    if (!Utils::isValidStatus($status)) {
        Utils::error('无效的状态');
    }
    if (!Utils::isValidDatetime($expireAt)) {
        Utils::error('请选择有效的有效期');
    }
    if ($status === 2 && $usedBy === '') {
        Utils::error('状态为已使用时，使用人不能为空');
    }

    $db = Database::getInstance();

    $record = $db->fetchOne(
        'SELECT id FROM `invitation_codes` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1',
        array(':id' => $id)
    );
    if (!$record) {
        Utils::error('记录不存在或已删除');
    }

    $updateData = array(
        'status'    => $status,
        'expire_at' => $expireAt,
        'used_by'   => $usedBy === '' ? null : $usedBy,
        'remark'    => $remark === '' ? null : $remark,
    );

    $affected = $db->update(
        'invitation_codes',
        $updateData,
        '`id` = :where_id',
        array(':where_id' => $id)
    );

    AdminLog::record('invitation', 'update', '编辑邀请码', array(
        'id' => $id,
    ));

    Utils::success('更新成功', array('affected' => $affected));
} catch (Exception $e) {
    Utils::error('更新失败: ' . $e->getMessage());
}
