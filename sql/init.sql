-- =============================================
-- 邀请码管理系统 数据库初始化脚本
-- 版本: 1.0
-- 日期: 2026-06-20
-- =============================================

-- 创建数据库（如不存在）
-- CREATE DATABASE IF NOT EXISTS `invitation_code` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `invitation_code`;

-- =============================================
-- 1. 邀请码表
-- =============================================
DROP TABLE IF EXISTS `invitation_codes`;

CREATE TABLE `invitation_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `code` VARCHAR(32) NOT NULL COMMENT '邀请码(唯一)',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:1未使用 2已使用 3已过期',
  `expire_at` DATETIME NOT NULL COMMENT '有效期',
  `used_by` VARCHAR(64) DEFAULT NULL COMMENT '使用人',
  `remark` VARCHAR(255) DEFAULT NULL COMMENT '备注',
  `is_deleted` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除标记:0否 1是',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请码表';

-- =============================================
-- 2. 初始测试数据
-- =============================================
INSERT INTO `invitation_codes` (`code`, `status`, `expire_at`, `used_by`, `remark`) VALUES
('TEST001', 1, '2026-12-31 23:59:59', NULL, '测试邀请码-未使用'),
('TEST002', 2, '2026-06-30 23:59:59', 'user_a@example.com', '测试邀请码-已使用'),
('TEST003', 3, '2025-12-31 23:59:59', NULL, '测试邀请码-已过期'),
('WELCOME2026', 1, '2026-06-30 23:59:59', NULL, '新用户欢迎码'),
('VIP888888', 1, '2027-01-01 00:00:00', NULL, 'VIP会员邀请码');
