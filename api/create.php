<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Utils.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/AdminLog.php';

Auth::requirePermission('invitation:create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::error('请求方式错误');
}

try {
    $code     = trim((string)Utils::input('code', ''));
    $expireAt = trim((string)Utils::input('expire_at', ''));
    $remark   = trim((string)Utils::input('remark', ''));

    if (!Utils::isValidDatetime($expireAt)) {
        Utils::error('请选择有效的有效期');
    }

    $db = Database::getInstance();

    if ($code === '') {
        $code = Utils::generateUniqueCode($db);
        if ($code === null) {
            Utils::error('生成邀请码失败，请重试');
        }
    } else {
        if (strlen($code) > 32) {
            Utils::error('邀请码长度不能超过32个字符');
        }
        $exists = $db->fetchOne(
            'SELECT id FROM `invitation_codes` WHERE `code` = :code LIMIT 1',
            array(':code' => $code)
        );
        if ($exists) {
            Utils::error('邀请码已存在');
        }
    }

    $insertId = $db->insert('invitation_codes', array(
        'code'      => $code,
        'status'    => 1,
        'expire_at' => $expireAt,
        'used_by'   => null,
        'remark'    => $remark === '' ? null : $remark,
    ));

    AdminLog::record('invitation', 'create', '新增邀请码', array(
        'id' => $insertId,
        'code' => $code,
    ));

    Utils::success('添加成功', array('id' => $insertId, 'code' => $code));
} catch (Exception $e) {
    Utils::error('添加失败: ' . $e->getMessage());
}
