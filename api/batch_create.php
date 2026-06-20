<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $count    = max(1, min(1000, (int)Utils::input('count', 0)));
    $expireAt = trim((string)Utils::input('expire_at', ''));
    $remark   = trim((string)Utils::input('remark', ''));

    if ($count <= 0) {
        Utils::error('请输入有效的生成数量（1-1000）');
    }
    if (!Utils::isValidDatetime($expireAt)) {
        Utils::error('请选择有效的有效期');
    }

    $db     = Database::getInstance();
    $remarkValue = $remark === '' ? null : $remark;

    $db->beginTransaction();

    $successCount = 0;
    for ($i = 0; $i < $count; $i++) {
        $code = Utils::generateUniqueCode($db);
        if ($code === null) {
            $db->rollBack();
            Utils::error('生成邀请码失败，请重试');
        }
        $db->insert('invitation_codes', array(
            'code'      => $code,
            'status'    => 1,
            'expire_at' => $expireAt,
            'used_by'   => null,
            'remark'    => $remarkValue,
        ));
        $successCount++;
    }

    $db->commit();

    AdminLog::record('invitation', 'batch_create', '批量生成邀请码', array(
        'count' => $successCount,
    ));

    Utils::success("成功生成{$successCount}个邀请码", array('success_count' => $successCount));
} catch (Exception $e) {
    if (isset($db) && method_exists($db, 'rollBack')) {
        try {
            $db->rollBack();
        } catch (Exception $ignore) {
        }
    }
    Utils::error('批量生成失败: ' . $e->getMessage());
}
