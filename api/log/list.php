<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Utils.php';
require_once __DIR__ . '/../../includes/AdminLog.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requirePermission('log:view');

try {
    $page     = max(1, (int)Utils::input('page', 1));
    $pageSize = max(1, min(200, (int)Utils::input('page_size', 20)));
    $keyword  = trim((string)Utils::input('keyword', ''));
    $module   = trim((string)Utils::input('module', ''));
    $action   = trim((string)Utils::input('action', ''));

    $filters = array();
    if ($module) {
        $filters['module'] = $module;
    }
    if ($action) {
        $filters['action'] = $action;
    }
    if ($keyword) {
        $filters['keyword'] = $keyword;
    }

    $result = AdminLog::getList($page, $pageSize, $filters);

    $modules = AdminLog::getModules();
    $actions = AdminLog::getActions();

    foreach ($result['list'] as &$item) {
        $item['module_text'] = isset($modules[$item['module']]) ? $modules[$item['module']] : $item['module'];
        $item['action_text'] = isset($actions[$item['action']]) ? $actions[$item['action']] : $item['action'];
    }
    unset($item);

    Utils::success('获取成功', $result);
} catch (Exception $e) {
    Utils::error('获取失败: ' . $e->getMessage());
}
