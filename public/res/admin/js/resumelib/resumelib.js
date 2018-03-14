$(document).ready(function () {

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


    //配置swf文件的路径
    ZeroClipboard.config( {
        moviePath: '/res/common/js/zclip/ZeroClipboard.swf'
    });

    //全局方法
    ZeroClipboard.on('aftercopy', function() {
        jobConfirm.toast('复制成功');
    });

    //初始化
    new ZeroClipboard( $(".copy-button") );

});