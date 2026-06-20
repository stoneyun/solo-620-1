(function ($) {
    'use strict';

    var STATE = {
        page: 1,
        pageSize: 20,
        total: 0,
        keyword: '',
        list: [],
        allPerms: []
    };

    var modalMode = 'create';

    function loadPerms(callback) {
        App.ajax('api/permission/all.php', {
            success: function (res) {
                if (res.code === 0 && res.data) {
                    STATE.allPerms = res.data;
                    renderPermCheckboxTree();
                    callback && callback();
                }
            }
        });
    }

    function renderPermCheckboxTree() {
        var $container = $('#permGroups');
        var html = '';

        $.each(STATE.allPerms, function (i, group) {
            html += '<div class="perm-group" style="margin-bottom:16px;">';
            html += '<p style="margin:0 0 8px;"><label style="margin-bottom:0;"><input type="checkbox" class="group-check" data-group="' + group.key + '"> <strong>' + group.name + '</strong></label></p>';
            html += '<div style="padding-left:24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;">';
            $.each(group.permissions, function (j, perm) {
                html += '<label style="margin-bottom:0;font-weight:normal;"><input type="checkbox" class="perm-check" data-group="' + group.key + '" value="' + perm.key + '"> ' + perm.name + '</label>';
            });
            html += '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    function loadList() {
        var params = {
            page: STATE.page,
            page_size: STATE.pageSize,
            keyword: STATE.keyword
        };

        App.ajax('api/group/list.php', {
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
            html += '<tr>';
            html += '<td>' + item.id + '</td>';
            html += '<td><strong>' + App.escapeHtml(item.name) + '</strong></td>';
            html += '<td>' + App.escapeHtml(item.description || '-') + '</td>';
            html += '<td><span class="badge badge-info" style="background-color:#3b82f6;">' + item.perm_count + '</span></td>';
            html += '<td>' + App.escapeHtml(item.created_at) + '</td>';
            html += '<td>';
            html += '<button class="btn btn-default btn-xs btn-edit" data-id="' + item.id + '" data-permission="group:edit" style="margin-right:4px;">编辑</button>';
            html += '<button class="btn btn-danger btn-xs btn-delete" data-id="' + item.id + '" data-permission="group:delete">删除</button>';
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
        if (!App.requirePermission('group:create')) return;
        modalMode = 'create';
        $('#groupId').val('');
        $('#groupName').val('');
        $('#groupDescription').val('');
        $('.perm-check, .group-check, #checkAllPerms').prop('checked', false);
        $('#groupModalTitle').html('<i class="fa fa-plus-circle" style="color:#1e3a8a;"></i> 添加分组');
        $('#submitGroup').text('确认添加');
        $('#groupModal').modal('show');
    }

    function showEditModal(id) {
        if (!App.requirePermission('group:edit')) return;
        var record = null;
        $.each(STATE.list, function (i, item) {
            if (item.id == id) {
                record = item;
                return false;
            }
        });
        if (!record) return;

        modalMode = 'edit';
        $('#groupId').val(record.id);
        $('#groupName').val(record.name);
        $('#groupDescription').val(record.description || '');

        $('.perm-check, .group-check, #checkAllPerms').prop('checked', false);
        $.each(record.permissions, function (i, perm) {
            $('.perm-check[value="' + perm + '"]').prop('checked', true);
        });
        updateGroupCheckStatus();
        updateCheckAllStatus();

        $('#groupModalTitle').html('<i class="fa fa-pencil" style="color:#f59e0b;"></i> 编辑分组');
        $('#submitGroup').text('保存修改');
        $('#groupModal').modal('show');
    }

    function updateGroupCheckStatus() {
        $.each(STATE.allPerms, function (i, group) {
            var $groupCheck = $('.group-check[data-group="' + group.key + '"]');
            var $permChecks = $('.perm-check[data-group="' + group.key + '"]');
            var total = $permChecks.length;
            var checked = $permChecks.filter(':checked').length;
            $groupCheck.prop('checked', total > 0 && checked === total);
            $groupCheck.prop('indeterminate', checked > 0 && checked < total);
        });
    }

    function updateCheckAllStatus() {
        var total = $('.perm-check').length;
        var checked = $('.perm-check:checked').length;
        $('#checkAllPerms').prop('checked', total > 0 && checked === total);
        $('#checkAllPerms').prop('indeterminate', checked > 0 && checked < total);
    }

    function getSelectedPerms() {
        var perms = [];
        $('.perm-check:checked').each(function () {
            perms.push($(this).val());
        });
        return perms;
    }

    function submitGroup() {
        var name = $.trim($('#groupName').val());
        var description = $.trim($('#groupDescription').val());
        var permissions = getSelectedPerms();

        if (!name) {
            App.toast('请输入分组名称', 'warning');
            return;
        }

        var url = modalMode === 'create' ? 'api/group/create.php' : 'api/group/update.php';
        var data = {
            name: name,
            description: description,
            permissions: permissions
        };
        if (modalMode === 'edit') {
            data.id = $('#groupId').val();
        }

        var $btn = $('#submitGroup');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 保存中...');

        App.ajax(url, {
            type: 'POST',
            traditional: true,
            data: data,
            success: function (res) {
                if (res.code === 0) {
                    App.toast(modalMode === 'create' ? '添加成功' : '更新成功', 'success');
                    $('#groupModal').modal('hide');
                    STATE.page = 1;
                    loadList();
                } else {
                    App.toast(res.message || '操作失败', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text(modalMode === 'create' ? '确认添加' : '保存修改');
            }
        });
    }

    function doDelete(id) {
        if (!App.requirePermission('group:delete')) return;
        App.confirmDialog('确认删除', '确定要删除该分组吗？如果分组下还有管理员则无法删除。', function () {
            App.ajax('api/group/delete.php', {
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
            STATE.page = 1;
            loadList();
        });

        $('#searchInput').on('keypress', function (e) {
            if (e.which === 13) {
                $('#searchBtn').click();
            }
        });

        $('#createBtn').on('click', showCreateModal);
        $('#submitGroup').on('click', submitGroup);

        $('#tableBody').on('click', '.btn-edit', function () {
            showEditModal($(this).data('id'));
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

        $('#checkAllPerms').on('change', function () {
            var checked = $(this).prop('checked');
            $('.perm-check, .group-check').prop('checked', checked);
        });

        $('#permGroups').on('change', '.group-check', function () {
            var groupKey = $(this).data('group');
            var checked = $(this).prop('checked');
            $('.perm-check[data-group="' + groupKey + '"]').prop('checked', checked);
            updateCheckAllStatus();
        });

        $('#permGroups').on('change', '.perm-check', function () {
            updateGroupCheckStatus();
            updateCheckAllStatus();
        });
    }

    $(function () {
        App.init(function () {
            loadPerms(function () {
                loadList();
                bindEvents();
            });
        });
    });

})(jQuery);
