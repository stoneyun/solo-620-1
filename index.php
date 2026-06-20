<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Utils.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>邀请码管理系统</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-static-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">切换导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">
                    <i class="fa fa-ticket" style="margin-right:8px;"></i>邀请码管理系统
                </a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="index.php"><i class="fa fa-home"></i> 首页</a></li>
                    <li><a href="javascript:;" id="refreshBtn"><i class="fa fa-refresh"></i> 刷新</a></li>
                    <li><a href="javascript:;" onclick="location.reload()"><i class="fa fa-user-circle-o"></i> 管理员</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="app-card">
            <div class="card-header">
                <h3><i class="fa fa-list-alt" style="margin-right:6px;color:#1e3a8a;"></i>邀请码列表</h3>
            </div>

            <div class="toolbar">
                <div class="toolbar-left">
                    <div class="search-input-group">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="搜索邀请码..." value="">
                    </div>
                    <select id="statusFilter" class="form-control" style="width:140px;">
                        <option value="0">全部状态</option>
                        <option value="1">未使用</option>
                        <option value="2">已使用</option>
                        <option value="3">已过期</option>
                    </select>
                    <button class="btn btn-default btn-sm" id="searchBtn">
                        <i class="fa fa-filter"></i> 筛选
                    </button>
                </div>
                <div class="toolbar-right">
                    <button class="btn btn-danger btn-sm" id="batchDeleteBtn">
                        <i class="fa fa-trash-o"></i> 批量删除
                    </button>
                    <button class="btn btn-warning btn-sm" id="batchCreateBtn">
                        <i class="fa fa-cubes"></i> 批量生成
                    </button>
                    <button class="btn btn-primary btn-sm" id="createBtn">
                        <i class="fa fa-plus"></i> 添加邀请码
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th class="checkbox-column">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>邀请码</th>
                            <th>状态</th>
                            <th>有效期</th>
                            <th>使用人</th>
                            <th>备注</th>
                            <th>创建时间</th>
                            <th style="width:140px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    </tbody>
                </table>
            </div>

            <div id="emptyState" class="empty-state" style="display:none;">
                <div class="empty-icon"><i class="fa fa-inbox"></i></div>
                <p>暂无数据</p>
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
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle" style="color:#1e3a8a;"></i> 添加邀请码</h4>
                </div>
                <div class="modal-body">
                    <form id="createForm">
                        <div class="auto-generate-wrap">
                            <label class="switch-label">
                                <input type="checkbox" id="autoGenerate" checked>
                                自动生成邀请码
                            </label>
                        </div>
                        <div class="form-group" id="codeGroup" style="display:none;">
                            <label>邀请码 <span class="required">*</span></label>
                            <input type="text" class="form-control" id="createCode" maxlength="32" placeholder="请输入自定义邀请码">
                            <span class="help-block">自定义邀请码，最多32个字符</span>
                        </div>
                        <div class="form-group">
                            <label>有效期 <span class="required">*</span></label>
                            <input type="datetime-local" class="form-control" id="createExpireAt">
                        </div>
                        <div class="form-group">
                            <label>备注</label>
                            <textarea class="form-control" id="createRemark" rows="2" maxlength="255" placeholder="选填"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="submitCreate">确定添加</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o" style="color:#f59e0b;"></i> 编辑邀请码</h4>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId">
                        <div class="form-group">
                            <label>邀请码</label>
                            <input type="text" class="form-control" id="editCode" readonly style="background-color:#f8fafc;">
                        </div>
                        <div class="form-group">
                            <label>状态 <span class="required">*</span></label>
                            <select class="form-control" id="editStatus">
                                <option value="1">未使用</option>
                                <option value="2">已使用</option>
                                <option value="3">已过期</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>有效期 <span class="required">*</span></label>
                            <input type="datetime-local" class="form-control" id="editExpireAt">
                        </div>
                        <div class="form-group" id="editUsedByGroup">
                            <label id="editUsedByLabel">使用人</label>
                            <input type="text" class="form-control" id="editUsedBy" maxlength="64" placeholder="状态为已使用时必填">
                        </div>
                        <div class="form-group">
                            <label>备注</label>
                            <textarea class="form-control" id="editRemark" rows="2" maxlength="255" placeholder="选填"></textarea>
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

    <div class="modal fade" id="batchCreateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-cubes" style="color:#f59e0b;"></i> 批量生成邀请码</h4>
                </div>
                <div class="modal-body">
                    <form id="batchCreateForm">
                        <div class="form-group">
                            <label>生成数量 <span class="required">*</span></label>
                            <input type="number" class="form-control" id="batchCount" min="1" max="1000" value="10" placeholder="1-1000">
                            <span class="help-block">单次最多生成1000个</span>
                        </div>
                        <div class="form-group">
                            <label>统一有效期 <span class="required">*</span></label>
                            <input type="datetime-local" class="form-control" id="batchExpireAt">
                        </div>
                        <div class="form-group">
                            <label>统一备注</label>
                            <textarea class="form-control" id="batchRemark" rows="2" maxlength="255" placeholder="选填，所有邀请码使用同一备注"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-warning" id="submitBatchCreate">开始生成</button>
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

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
