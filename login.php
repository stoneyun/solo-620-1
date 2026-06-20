<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Utils.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 邀请码管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body.login-page {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            animation: fadeInUp 0.6s ease;
        }
        .login-box .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-box .login-logo .icon-circle {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
        }
        .login-box .login-logo h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
        }
        .login-box .login-logo p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .login-box .form-group {
            margin-bottom: 20px;
        }
        .login-box .form-control {
            height: 42px;
            padding-left: 40px;
            border-radius: 6px;
        }
        .login-box .input-icon {
            position: relative;
        }
        .login-box .input-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }
        .login-box .btn-login {
            width: 100%;
            height: 42px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .login-box .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
        }
        .login-box .btn-login:active {
            transform: translateY(0);
        }
        .login-box .login-tips {
            margin-top: 16px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
        .login-box .login-tips code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            color: #475569;
        }
        .toast-container {
            top: 24px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="icon-circle"><i class="fa fa-ticket"></i></div>
            <h2>邀请码管理系统</h2>
            <p>Invitation Code Management System</p>
        </div>
        <form id="loginForm">
            <div class="form-group">
                <label>用户名</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="form-control" id="username" placeholder="请输入用户名" autocomplete="username">
                </div>
            </div>
            <div class="form-group">
                <label>密码</label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password" class="form-control" id="password" placeholder="请输入密码" autocomplete="current-password">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="fa fa-sign-in"></i> 登 录
                </button>
            </div>
            <div class="login-tips">
                默认账号：<code>admin</code> / <code>admin123456</code>
            </div>
        </form>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <script>
    (function ($) {
        'use strict';

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function toast(message, type) {
            type = type || 'info';
            var iconMap = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            var titleMap = { success: '成功', error: '错误', warning: '警告', info: '提示' };

            var $toast = $([
                '<div class="toast toast-' + type + '">',
                '  <i class="fa ' + iconMap[type] + ' toast-icon"></i>',
                '  <div class="toast-body">',
                '    <p class="toast-title">' + titleMap[type] + '</p>',
                '    <p class="toast-msg">' + escapeHtml(message) + '</p>',
                '  </div>',
                '</div>'
            ].join(''));

            $('#toastContainer').append($toast);

            setTimeout(function () {
                $toast.addClass('toast-out');
                setTimeout(function () { $toast.remove(); }, 300);
            }, 3000);
        }

        function doLogin() {
            var username = $.trim($('#username').val());
            var password = $('#password').val();
            var $btn = $('#loginBtn');

            if (!username) {
                toast('请输入用户名', 'warning');
                return;
            }
            if (!password) {
                toast('请输入密码', 'warning');
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 登录中...');

            $.ajax({
                url: 'api/admin/login.php',
                type: 'POST',
                dataType: 'json',
                data: { username: username, password: password },
                success: function (res) {
                    if (res.code === 0) {
                        toast('登录成功，正在跳转...', 'success');
                        setTimeout(function () {
                            location.href = 'index.php';
                        }, 600);
                    } else {
                        toast(res.message || '登录失败', 'error');
                    }
                },
                error: function (xhr) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        toast(res.message || '登录失败', 'error');
                    } catch (e) {
                        toast('网络错误，请稍后重试', 'error');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-sign-in"></i> 登 录');
                }
            });
        }

        $(function () {
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                doLogin();
            });

            $('#username').focus();
        });

    })(jQuery);
    </script>
</body>
</html>
