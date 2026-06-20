<?php
define('IN_SYSTEM', true);
$page_title = '分组权限';
$current_page = 'group';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Utils.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/layout/header.php';
?>
<div class="app-card">
    <div class="card-header">
        <h3><i class="fa fa-sitemap" style="margin-right:6px;color:#1e3a8a;"></i>分组权限管理</h3>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-input-group">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="搜索分组名称..." value="">
            </div>
            <button class="btn btn-default btn-sm" id="searchBtn">
                <i class="fa fa-filter"></i> 搜索
            </button>
        </div>
        <div class="toolbar-right">
            <button class="btn btn-primary btn-sm" id="createBtn" data-permission="group:create">
                <i class="fa fa-plus"></i> 添加分组
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover" id="dataTable">
            <thead>
                <tr>
                    <th style="width:80px;">ID</th>
                    <th>分组名称</th>
                    <th>描述</th>
                    <th style="width:120px;">权限数</th>
                    <th style="width:180px;">创建时间</th>
                    <th style="width:180px;">操作</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="empty-icon"><i class="fa fa-sitemap"></i></div>
        <p>暂无分组数据</p>
    </div>

    <div class="pagination-wrap">
        <div class="pagination-info" id="paginationInfo"></div>
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
            <ul class="pagination" id="pagination"></ul>
            <div class="page-jump">
                <span>跳至</span>
                <input type="number" id="jumpPage" class="form-control" min="1" value="1">
                <span>页</span>
                <button class="btn btn-default btn-sm" id="jumpBtn">GO</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="groupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="groupModalTitle"><i class="fa fa-plus-circle" style="color:#1e3a8a;"></i> 添加分组</h4>
            </div>
            <div class="modal-body">
                <form id="groupForm">
                    <input type="hidden" id="groupId">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>分组名称 <span class="required">*</span></label>
                                <input type="text" class="form-control" id="groupName" maxlength="64" placeholder="请输入分组名称">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>描述</label>
                                <input type="text" class="form-control" id="groupDescription" maxlength="255" placeholder="选填">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>功能权限分配</label>
                        <div id="permissionContainer" style="border:1px solid #e2e8f0;border-radius:6px;padding:16px;max-height:400px;overflow-y:auto;background:#fafbfc;">
                            <p style="color:#64748b;margin:0 0 12px;">
                                <label style="margin-bottom:0;">
                                    <input type="checkbox" id="checkAllPerms">
                                    <strong> 全选</strong>
                                </label>
                            </p>
                            <div id="permGroups"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submitGroup">确认保存</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="confirmTitle"><i class="fa fa-question-circle" style="color:#ef4444;"></i> 确认操作</h4>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmOk">确定</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/groups.js"></script>
<?php
require_once __DIR__ . '/includes/layout/footer.php';
