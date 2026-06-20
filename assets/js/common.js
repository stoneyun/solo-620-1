(function ($) {
    'use strict';

    var App = {
        currentUser: null,
        permissions: [],

        escapeHtml: function (str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        },

        toast: function (message, type) {
            type = type || 'info';
            var iconMap = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            var titleMap = { success: '成功', error: '错误', warning: '警告', info: '提示' };

            var html = [
                '<div class="toast toast-' + type + '">',
                '  <i class="fa ' + iconMap[type] + ' toast-icon"></i>',
                '  <div class="toast-body">',
                '    <p class="toast-title">' + titleMap[type] + '</p>',
                '    <p class="toast-msg">' + this.escapeHtml(message) + '</p>',
                '  </div>',
                '</div>'
            ].join('');

            var $toast = $(html);
            $('#toastContainer').append($toast);

            setTimeout(function () {
                $toast.addClass('toast-out');
                setTimeout(function () { $toast.remove(); }, 300);
            }, 3000);
        },

        confirmDialog: function (title, message, onOk) {
            var $modal = $('#confirmModal');
            if ($modal.length === 0) {
                if (window.confirm(message)) {
                    onOk && onOk();
                }
                return;
            }
            $modal.find('#confirmTitle').html('<i class="fa fa-question-circle" style="color:#ef4444;"></i> ' + title);
            $modal.find('#confirmMessage').text(message);
            $modal.modal('show');
            $modal.off('click', '#confirmOk');
            $modal.on('click', '#confirmOk', function () {
                $modal.modal('hide');
                onOk && onOk();
            });
        },

        ajax: function (url, options) {
            options = options || {};
            var self = this;

            return $.ajax($.extend(true, {
                url: url,
                type: 'GET',
                dataType: 'json',
                error: function (xhr) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.code === 401) {
                            self.toast('登录已过期，请重新登录', 'warning');
                            setTimeout(function () {
                                location.href = 'login.php';
                            }, 1000);
                            return;
                        }
                        self.toast(res.message || '请求失败', 'error');
                    } catch (e) {
                        self.toast('网络错误，请稍后重试', 'error');
                    }
                }
            }, options));
        },

        initAuth: function (callback) {
            var self = this;
            this.ajax('api/admin/current.php', {
                type: 'GET',
                success: function (res) {
                    if (res.code === 0) {
                        self.currentUser = res.data.user;
                        self.permissions = res.data.permissions || [];
                        $('#navUsername').text(self.currentUser.username || '未知');
                        callback && callback(null, res.data);
                    } else {
                        location.href = 'login.php';
                    }
                },
                error: function (xhr) {
                    location.href = 'login.php';
                }
            });
        },

        hasPermission: function (perm) {
            if (!perm) return true;
            if (this.currentUser && this.currentUser.is_super) return true;
            return $.inArray(perm, this.permissions) > -1;
        },

        requirePermission: function (perm) {
            if (!this.hasPermission(perm)) {
                this.toast('没有操作权限', 'warning');
                return false;
            }
            return true;
        },

        applyPermissionUI: function () {
            var self = this;
            $('[data-permission]').each(function () {
                var perm = $(this).data('permission');
                if (!self.hasPermission(perm)) {
                    $(this).hide();
                }
            });
        },

        formatDateTime: function (datetime) {
            if (!datetime) return '-';
            return datetime;
        },

        doLogout: function () {
            var self = this;
            this.confirmDialog('退出确认', '确定要退出登录吗？', function () {
                self.ajax('api/admin/logout.php', {
                    type: 'POST',
                    success: function (res) {
                        if (res.code === 0) {
                            self.toast('已退出登录', 'success');
                            setTimeout(function () {
                                location.href = 'login.php';
                            }, 500);
                        }
                    }
                });
            });
        },

        doChangePassword: function () {
            var $modal = $('#changePasswordModal');
            $modal.find('#cpOldPassword, #cpNewPassword, #cpConfirmPassword').val('');
            $modal.modal('show');
        },

        submitChangePassword: function () {
            var self = this;
            var oldPwd = $('#cpOldPassword').val();
            var newPwd = $('#cpNewPassword').val();
            var confirmPwd = $('#cpConfirmPassword').val();

            if (!oldPwd) {
                this.toast('请输入当前密码', 'warning');
                return;
            }
            if (!newPwd) {
                this.toast('请输入新密码', 'warning');
                return;
            }
            if (newPwd.length < 6) {
                this.toast('新密码长度不能少于6位', 'warning');
                return;
            }
            if (newPwd !== confirmPwd) {
                this.toast('两次输入的新密码不一致', 'warning');
                return;
            }

            var $btn = $('#submitChangePassword');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 提交中...');

            this.ajax('api/admin/change_password.php', {
                type: 'POST',
                data: {
                    old_password: oldPwd,
                    new_password: newPwd,
                    confirm_password: confirmPwd
                },
                success: function (res) {
                    if (res.code === 0) {
                        self.toast('密码修改成功，请重新登录', 'success');
                        $('#changePasswordModal').modal('hide');
                        setTimeout(function () {
                            location.href = 'login.php';
                        }, 1000);
                    } else {
                        self.toast(res.message || '修改失败', 'error');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).text('确认修改');
                }
            });
        },

        renderPagination: function ($container, total, page, pageSize, onPageChange) {
            var totalPages = Math.ceil(total / pageSize) || 1;
            $container.empty();

            $container.append('<li class="' + (page === 1 ? 'disabled' : '') + '"><a href="javascript:;" data-page="' + (page - 1) + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>');

            var startPage = Math.max(1, page - 2);
            var endPage = Math.min(totalPages, page + 2);

            if (startPage > 1) {
                $container.append('<li><a href="javascript:;" data-page="1">1</a></li>');
                if (startPage > 2) {
                    $container.append('<li class="disabled"><span>...</span></li>');
                }
            }

            for (var i = startPage; i <= endPage; i++) {
                $container.append('<li class="' + (i === page ? 'active' : '') + '"><a href="javascript:;" data-page="' + i + '">' + i + '</a></li>');
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    $container.append('<li class="disabled"><span>...</span></li>');
                }
                $container.append('<li><a href="javascript:;" data-page="' + totalPages + '">' + totalPages + '</a></li>');
            }

            $container.append('<li class="' + (page === totalPages ? 'disabled' : '') + '"><a href="javascript:;" data-page="' + (page + 1) + '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>');

            $container.off('click', 'a[data-page]');
            $container.on('click', 'a[data-page]', function (e) {
                e.preventDefault();
                var p = parseInt($(this).data('page'));
                if (p < 1 || p > totalPages || p === page) return;
                onPageChange && onPageChange(p);
            });
        },

        init: function (callback) {
            var self = this;
            this.initAuth(function (err, data) {
                if (!err) {
                    self.applyPermissionUI();
                }
                callback && callback(err, data);
            });

            $('#logoutBtn').on('click', function () {
                self.doLogout();
            });

            $('#changePasswordBtn').on('click', function () {
                self.doChangePassword();
            });

            $('#submitChangePassword').on('click', function () {
                self.submitChangePassword();
            });
        }
    };

    window.App = App;

    $(function () {
        App.init();
    });

})(jQuery);
