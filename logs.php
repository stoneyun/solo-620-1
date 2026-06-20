<?php
define('IN_SYSTEM', true);
$page_title = '操作日志';
$current_page = 'log';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Utils.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/layout/header.php';
?>
<div class="app-card">
    <div class="card-header">
        <h3><i class="fa fa-file-text-o" style="margin-right:6px;color:#1e3a8a;"></i>操作日志</h3>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-input-group">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="searchInput" class="form-control" placeholder="搜索操作人/操作描述..." value="">
            </div>
            <select id="moduleFilter" class="form-control" style="width:140px;">
                <option value="">全部模块</option>
            </select>
            <select id="actionFilter" class="form-control" style="width:120px;">
                <option value="">全部操作</option>
            </select>
            <button class="btn btn-default btn-sm" id="searchBtn">
                <i class="fa fa-filter"></i> 筛选
            </button>
        </div>
        <div class="toolbar-right">
            <button class="btn btn-default btn-sm" id="refreshBtn">
                <i class="fa fa-refresh"></i> 刷新
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover" id="dataTable">
            <thead>
                <tr>
                    <th style="width:80px;">ID</th>
                    <th style="width:120px;">操作时间</th>
                    <th style="width:100px;">操作人</th>
                    <th style="width:90px;">模块</th>
                    <th style="width:90px;">操作</th>
                    <th>描述</th>
                    <th style="width:130px;">IP地址</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </div>

    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="empty-icon"><i class="fa fa-file-text-o"></i></div>
        <p>暂无日志数据</p>
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

<script src="assets/js/logs.js"></script>
<?php
require_once __DIR__ . '/includes/layout/footer.php';
