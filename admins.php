<?php
define('IN_SYSTEM', true);
$page_title = '管理员管理';
$current_page = 'admin';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Utils.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/layout/header.php';
?>
<div class="app-card">
    <div class="card-header">
        <h3><i class="fa fa-users" style="margin-right:6px;color:#1e3a8a;"></i>管理员管理</h3>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-input-group">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="搜索用户名/姓名..." value="">
            </div>
            <select id="statusFilter" class="form-control" style="width:130px;">
                <option value="0">全部状态</option>
                <option value="1">启用</option>
                <option value="2">禁用</option>
            </select>
            <select id="groupFilter" class="form-control" style="width:150px;">
                <option value="0">全部分组</option>
            </select>
            <button class="btn btn-default btn-sm" id="searchBtn">
                <i class="fa fa-filter"></i> 筛选
            </button>
        </div>
        <div class="toolbar-right">
            <button class="btn btn-primary btn-sm" id="createBtn" data-permission="admin:create">
                <i class="fa fa-plus"></i> 添加管理员
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover" id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>姓名</th>
                    <th>分组</th>
                    <th>状态</th>
                    <th>最后登录</th>
                    <th>登录次数</th>
                    <th>创建时间</th>
                    <th style="width:220px;">操作</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="empty-icon"><i class="fa fa-users"></i></div>
        <p>暂无管理员数据</p>
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

<div class="modal fade" id="createModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-user-plus" style="color:#1e3a8a;"></i> 添加管理员</h4>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="form-group">
                        <label>用户名 <span class="required">*</span></label>
                        <input type="text" class="form-control" id="createUsername" maxlength="32" placeholder="请输入用户名">
                    </div>
                    <div class="form-group">
                        <label>姓名</label>
                        <input type="text" class="form-control" id="createRealName" maxlength="32" placeholder="选填">
                    </div>
                    <div class="form-group">
                        <label>所属分组 <span class="required">*</span></label>
                        <select class="form-control" id="createGroupId"></select>
                    </div>
                    <div class="form-group">
                        <label>初始密码 <span class="required">*</span></label>
                        <input type="password" class="form-control" id="createPassword" placeholder="最少6位">
                    </div>
                    <div class="form-group">
                        <label>状态</label>
                        <select class="form-control" id="createStatus">
                            <option value="1">启用</option>
                            <option value="2">禁用</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submitCreate">确认添加</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil" style="color:#f59e0b;"></i> 编辑管理员</h4>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" class="form-control" id="editUsername" readonly style="background-color:#f8fafc;">
                    </div>
                    <div class="form-group">
                        <label>姓名</label>
                        <input type="text" class="form-control" id="editRealName" maxlength="32" placeholder="选填">
                    </div>
                    <div class="form-group">
                        <label>所属分组 <span class="required">*</span></label>
                        <select class="form-control" id="editGroupId"></select>
                    </div>
                    <div class="form-group">
                        <label>状态</label>
                        <select class="form-control" id="editStatus">
                            <option value="1">启用</option>
                            <option value="2">禁用</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submitEdit">保存修改</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-key" style="color:#ef4444;"></i> 重置密码</h4>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm">
                    <input type="hidden" id="rpId">
                    <div class="form-group">
                        <label>管理员用户名</label>
                        <input type="text" class="form-control" id="rpUsername" readonly style="background-color:#f8fafc;">
                    </div>
                    <div class="form-group">
                        <label>新密码 <span class="required">*</span></label>
                        <input type="password" class="form-control" id="rpPassword" placeholder="请输入新密码，最少6位">
                    </div>
                    <div class="form-group">
                        <label>确认新密码 <span class="required">*</span></label>
                        <input type="password" class="form-control" id="rpConfirmPassword" placeholder="请再次输入新密码">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="submitResetPassword">确认重置</button>
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

<script src="assets/js/admins.js"></script>
<?php
require_once __DIR__ . '/includes/layout/footer.php';
