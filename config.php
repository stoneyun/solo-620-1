<?php
/**
 * 数据库配置文件
 * 请根据实际环境修改以下配置
 */

// 数据库主机
defined('DB_HOST') || define('DB_HOST', 'localhost');

// 数据库端口
defined('DB_PORT') || define('DB_PORT', 3306);

// 数据库名
defined('DB_NAME') || define('DB_NAME', 'invitation_code');

// 数据库用户名
defined('DB_USER') || define('DB_USER', 'root');

// 数据库密码
defined('DB_PASS') || define('DB_PASS', '');

// 数据库字符集
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// 邀请码默认长度（自动生成时使用）
defined('CODE_LENGTH') || define('CODE_LENGTH', 8);

// 邀请码字符集（自动生成时使用）
defined('CODE_CHARS') || define('CODE_CHARS', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告（生产环境建议关闭显示）
// ini_set('display_errors', 0);
// error_reporting(E_ALL);
