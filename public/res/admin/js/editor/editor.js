/**
 * Created by user on 2015/9/28.
 */
(function () {
    var editor = {
        //弹窗的表单
        $form: $('#j-exp-form'),
        target: null,

        init: function () {
            this.bindEvent();
        },
        bindEvent: function () {
            var _this = this;

            //点击编辑
            $('body').delegate('.j-edit', 'click', function () {
                _this.target = this;
                var _url = $(this).attr('data-target');

                //弹出弹窗
                _this.dialog();

                //加载数据
                _this.getData({
                    url: _url,
                    data: ""
                });

            });

            //点击弹窗的取消
            $('#j-exp-cancel').click(function () {
                _this.closeDialog();
            });

            //点击弹窗的确定，表单提交
            this.$form.submit(function () {
                _this.submit();
                return false;
            });
        },
        dialog: function () {
            $.cxDialog({
                background: 'rgba(0,0,0,0.7)',
                baseClass: 'editor exp_dig',
                info: $('#j-exp-dig'),
                width: 800
            });
        },
        toast: function (msg) {
            $.cxDialog({
                background: 'rgba(0,0,0,0.7)',
                baseClass: 'toast',
                info: msg
            });

            setTimeout(function () {
                $.cxDialog.close();
            }, 1000);
        },
        closeDialog: function () {
            $.cxDialog.close();
            this.$form.find(':input:not(:submit,:button)').val('');
            this.target = null;
            window.location.reload(true);
        },
        getData: function (obj) {
            //点击编辑时获取数据
            var _this = this;
            $.ajax({
                url: obj.url,
                type: "post",
                dataType: 'json',
                data: obj.data,
                success: function (result) {
                    _this.setData(result, _this);
                }
            });
        },
        setData: function (result) {
            //获取数据成功的操作：把对应的数据填入表单中
            var _this = this;
            if (result.code == 200) {
                $.each(result.result, function (key, value) {
                    if (_this.$form.find('[name=' + key + ']')) {
                        _this.$form.find('[name=' + key + ']').val(value);
                    }
                });
            }
        },
        submit: function () {
            //表单提交的操作
            var _this = this;
            var data = _this.$form.serialize();
            var _url = _this.$form.find('input:submit').attr('data-target');
            $.ajax({
                url: _url,
                type: "post",
                dataType: 'json',
                data: data,
                success: function (res) {
                    if(res.code == 200){
                        _this.toast(res.msg);
                        _this.setHtml();   //提交成功后设置html的内容
                        _this.closeDialog();
                    }else{
                        _this.toast(res.msg);
                    }
                },
                error: function (e) {
                    _this.toast(e.msg)
                }
            });
        },
        //设置html的内容,测试
        setHtml: function (data) {
            $(this.target).siblings('span').html(this.$form.find('[name=nick]').val());
        }
    };

    $(function () {
        editor.init();
    });

})();