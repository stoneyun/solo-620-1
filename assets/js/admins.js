(function ($) {
    'use strict';

    var STATE = {
        page: 1,
        pageSize: 20,
        total: 0,
        keyword: '',
        status: 0,
        groupId: 0,
        list: [],
        groups: []
    };

    function loadGroups() {
        App.ajax('api/group/all.php', {
            success: function (res) {
                if (res.code === 0 && res.data) {
                    STATE.groups = res.data;
                    var html = '<option value="0">全部分组</option>';
                    var optHtml = '';
                    $.each(res.data, function (i, g) {
                        html += '<option value="' + g.id + '">' + App.escapeHtml(g.name) + '</option>';
                        optHtml += '<option value="' + g.id + '">' + App.escapeHtml(g.name) + '</option>';
                    });
                    $('#groupFilter').html(html);
                    $('#createGroupId').html(optHtml);
                    $('#editGroupId').html(optHtml);
                }
            }
        });
    }

    function loadList() {
        var params = {
            page: STATE.page,
            page_size: STATE.pageSize,
            keyword: STATE.keyword,
            status: STATE.status,
            group_id: STATE.groupId
        };

        App.ajax('api/admin/list.php', {
            data: params,
            success: function (res) {
                if (res.code === 0) {
                    STATE.total = res.data.total;
                    STATE.list = res.data.list;
                    renderTable();
                    renderPagination();
                }
            }
        });
    }

    function getGroupName(groupId) {
        var name = '-';
        $.each(STATE.groups, function (i, g) {
            if (g.id == groupId) {
                name = g.name;
                return false;
            }
        });
        return name;
    }

    function renderTable() {
        var $tbody = $('#tableBody');
        var $empty = $('#emptyState');

        if (STATE.list.length === 0) {
            $tbody.empty();
            $empty.show();
            $('#paginationInfo').text('共 0 条数据');
            return;
        }

        $empty.hide();
        var html = '';
        $.each(STATE.list, function (i, item) {
            var statusHtml = item.status == 1
                ? '<span class="label label-success">启用</span>'
                : '<span class="label label-default">禁用</span>';
            var isSuper = item.is_super == 1 ? ' <span class="label label-primary" style="margin-left:4px;">超管</span>' : '';
            var groupName = getGroupName(item.group_id);
            var lastLogin = item.last_login_at || '-';
            var loginCount = item.login_count || 0;

            html += '<tr>';
            html += '<td>' + item.id + '</td>';
            html += '<td><code>' + App.escapeHtml(item.username) + '</code>' + isSuper + '</td>';
            html += '<td>' + App.escapeHtml(item.real_name || '-') + '</td>';
            html += '<td>' + App.escapeHtml(groupName) + '</td>';
            html += '<td>' + statusHtml + '</td>';
            html += '<td>' + App.escapeHtml(lastLogin) + '</td>';
            html += '<td>' + loginCount + '</td>';
            html += '<td>' + App.escapeHtml(item.created_at) + '</td>';
            html += '<td>';
            html += '<button class="btn btn-default btn-xs btn-edit" data-id="' + item.id + '" data-permission="admin:edit" style="margin-right:4px;">编辑</button>';
            html += '<button class="btn btn-warning btn-xs btn-reset" data-id="' + item.id + '" data-permission="admin:edit" style="margin-right:4px;">重置密码</button>';
            html += '<button class="btn btn-danger btn-xs btn-delete" data-id="' + item.id + '" data-permission="admin:delete">删除</button>';
            html += '</td>';
            html += '</tr>';
        });

        $tbody.html(html);
        App.applyPermissionUI();
        $('#paginationInfo').text('共 ' + STATE.total + ' 条，第 ' + STATE.page + '/' + Math.ceil(STATE.total / STATE.pageSize) + ' 页');
    }

    function renderPagination() {
        App.renderPagination($('#pagination'), STATE.total, STATE.page, STATE.pageSize, function (page) {
            STATE.page = page;
            loadList();
        });
    }

    function showCreateModal() {
        if (!App.requirePermission('admin:create')) return;
        $('#createForm')[0].reset();
        $('#createStatus').val(1);
        if (STATE.groups.length > 0) {
            $('#createGroupId').val(STATE.groups[0].id);
        }
        $('#createModal').modal('show');
    }

    function submitCreate() {
        var data = {
            username: $.trim($('#createUsername').val()),
            real_name: $.trim($('#createRealName').val()),
            group_id: $('#createGroupId').val(),
            password: $('#createPassword').val(),
            status: $('#createStatus').val()
        };

        if (!data.username) {
            App.toast('请输入用户名', 'warning');
            return;
        }
        if (!data.password || data.password.length < 6) {
            App.toast('密码不能少于6位', 'warning');
            return;
        }

        var $btn = $('#submitCreate');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 提交中...');

        App.ajax('api/admin/create.php', {
            type: 'POST',
            data: data,
            success: function (res) {
                if (res.code === 0) {
                    App.toast('添加成功', 'success');
                    $('#createModal').modal('hide');
                    STATE.page = 1;
                    loadList();
                } else {
                    App.toast(res.message || '添加失败', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('确认添加');
            }
        });
    }

    function showEditModal(id) {
        if (!App.requirePermission('admin:edit')) return;
        var record = null;
        $.each(STATE.list, function (i, item) {
            if (item.id == id) {
                record = item;
                return false;
            }
        });
        if (!record) return;

        $('#editId').val(record.id);
        $('#editUsername').val(record.username);
        $('#editRealName').val(record.real_name || '');
        $('#editGroupId').val(record.group_id);
        $('#editStatus').val(record.status);
        $('#editModal').modal('show');
    }

    function submitEdit() {
        var data = {
            id: $('#editId').val(),
            real_name: $.trim($('#editRealName').val()),
            group_id: $('#editGroupId').val(),
            status: $('#editStatus').val()
        };

        var $btn = $('#submitEdit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 保存中...');

        App.ajax('api/admin/update.php', {
            type: 'POST',
            data: data,
            success: function (res) {
                if (res.code === 0) {
                    App.toast('更新成功', 'success');
                    $('#editModal').modal('hide');
                    loadList();
                } else {
                    App.toast(res.message || '更新失败', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('保存修改');
            }
        });
    }

    function showResetPasswordModal(id) {
        if (!App.requirePermission('admin:edit')) return;
        var record = null;
        $.each(STATE.list, function (i, item) {
            if (item.id == id) {
                record = item;
                return false;
            }
        });
        if (!record) return;

        $('#rpId').val(record.id);
        $('#rpUsername').val(record.username);
        $('#rpPassword, #rpConfirmPassword').val('');
        $('#resetPasswordModal').modal('show');
    }

    function submitResetPassword() {
        var id = $('#rpId').val();
        var pwd = $('#rpPassword').val();
        var confirmPwd = $('#rpConfirmPassword').val();

        if (!pwd || pwd.length < 6) {
            App.toast('密码不能少于6位', 'warning');
            return;
        }
        if (pwd !== confirmPwd) {
            App.toast('两次输入的密码不一致', 'warning');
            return;
        }

        var $btn = $('#submitResetPassword');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 处理中...');

        App.ajax('api/admin/reset_password.php', {
            type: 'POST',
            data: { id: id, password: pwd },
            success: function (res) {
                if (res.code === 0) {
                    App.toast('密码重置成功', 'success');
                    $('#resetPasswordModal').modal('hide');
                } else {
                    App.toast(res.message || '操作失败', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('确认重置');
            }
        });
    }

    function doDelete(id) {
        if (!App.requirePermission('admin:delete')) return;
        App.confirmDialog('确认删除', '确定要删除该管理员吗？删除后无法恢复。', function () {
            App.ajax('api/admin/delete.php', {
                type: 'POST',
                data: { id: id },
                success: function (res) {
                    if (res.code === 0) {
                        App.toast('删除成功', 'success');
                        loadList();
                    } else {
                        App.toast(res.message || '删除失败', 'error');
                    }
                }
            });
        });
    }

    function bindEvents() {
        $('#searchBtn').on('click', function () {
            STATE.keyword = $.trim($('#searchInput').val());
            STATE.status = $('#statusFilter').val();
            STATE.groupId = $('#groupFilter').val();
            STATE.page = 1;
            loadList();
        });

        $('#searchInput').on('keypress', function (e) {
            if (e.which === 13) {
                $('#searchBtn').click();
            }
        });

        $('#createBtn').on('click', showCreateModal);
        $('#submitCreate').on('click', submitCreate);
        $('#submitEdit').on('click', submitEdit);
        $('#submitResetPassword').on('click', submitResetPassword);

        $('#tableBody').on('click', '.btn-edit', function () {
            showEditModal($(this).data('id'));
        });
        $('#tableBody').on('click', '.btn-reset', function () {
            showResetPasswordModal($(this).data('id'));
        });
        $('#tableBody').on('click', '.btn-delete', function () {
            doDelete($(this).data('id'));
        });

        $('#jumpBtn').on('click', function () {
            var p = parseInt($('#jumpPage').val());
            var totalPages = Math.ceil(STATE.total / STATE.pageSize) || 1;
            if (p < 1 || p > totalPages) {
                App.toast('请输入有效的页码', 'warning');
                return;
            }
            STATE.page = p;
            loadList();
        });

        $('#refreshBtn').on('click', function () {
            loadList();
        });
    }

    $(function () {
        App.init(function () {
            loadGroups();
            loadList();
            bindEvents();
        });
    });

})(jQuery);
