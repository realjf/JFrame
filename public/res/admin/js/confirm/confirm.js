
jobConfirm = {};
jobConfirm.toast = function (infoText) {
    $.cxDialog({
        background: 'rgba(0,0,0,0.7)',
        width: 260,
        info: infoText
    });

    setTimeout(function () {
        $.cxDialog.close();
    }, 1000);
};

jobConfirm.delete = function (url, data, method){
    $.cxDialog({
        title: '提示',
        info: '确定删除该记录吗？',
        ok:function(){
            $.ajax({
                url: url + '?_AJAX_=' + Math.random(),
                dataType: 'json',
                type: method,
                data: data,
                success: function (res) {
                    if(res.code == 200){
                        window.location.reload();
                        jobConfirm.toast('操作成功');
                    } else{
                        jobConfirm.toast('操作失败');
                    }
                }
            });
        },
        no:function(){}
    });
}

jobConfirm.save = function (url, data, method){
    $.cxDialog({
        title: '提示',
        info: '确定保存该记录吗？',
        ok:function(){
            $.ajax({
                url: url + '?_AJAX_=' + Math.random(),
                dataType: 'json',
                type: method,
                data: data,
                success: function (res) {
                    if(res.code == 200){
                        window.location.reload();
                        jobConfirm.toast('操作成功');
                    } else{
                        jobConfirm.toast('操作失败');
                    }
                }
            });
        },
        no:function(){}
    });
}
