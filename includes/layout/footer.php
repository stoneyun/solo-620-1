    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-key" style="color:#1e3a8a;"></i> 修改密码</h4>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="form-group">
                            <label>当前密码 <span class="required">*</span></label>
                            <input type="password" class="form-control" id="cpOldPassword" placeholder="请输入当前密码">
                        </div>
                        <div class="form-group">
                            <label>新密码 <span class="required">*</span></label>
                            <input type="password" class="form-control" id="cpNewPassword" placeholder="请输入新密码，最少6位">
                        </div>
                        <div class="form-group">
                            <label>确认新密码 <span class="required">*</span></label>
                            <input type="password" class="form-control" id="cpConfirmPassword" placeholder="请再次输入新密码">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="submitChangePassword">确认修改</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/common.js"></script>
</body>
</html>
