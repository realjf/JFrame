/**
 * Created by user on 2015/9/10.
 */
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


    $(".remove").live('click', function () {
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title': '提示',
            'message': '确定删除该记录吗？',
            'buttons': {
                '确定': {
                    'class': 'blue',
                    'action': function () {
                        $.ajax({
                            url: '/admin/adminmgr/delete?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {id: id},
                            success: function (res) {
                                if(res.code == 200){
                                    window.location.reload();
                                    jobConfirm.toast(res.msg);
                                } else{
                                    jobConfirm.toast(res.msg);
                                }
                            }
                        });
                    }
                },
                '取消': {
                    'class': 'gray',
                    'action': function () {
                    }
                }
            }
        })

    });

    $(".blacklist").live('click', function () {
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title': '提示',
            'message': '确定将该用户加入黑名单吗？',
            'buttons': {
                '确定': {
                    'class': 'blue',
                    'action': function () {
                        $.ajax({
                            url: '/admin/adminmgr/blacklist?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {id: id, status: 1},
                            success: function (res) {
                                if(res.code == 200){
                                    window.location.reload();
                                    jobConfirm.toast(res.msg);
                                } else{
                                    jobConfirm.toast(res.msg);
                                }
                            }
                        });
                    }
                },
                '取消': {
                    'class': 'gray',
                    'action': function () {
                    }
                }
            }
        });

    });

    $(".deblacklist").live('click', function () {
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title': '提示',
            'message': '确定将该用户从黑名单中移除吗？',
            'buttons': {
                '确定': {
                    'class': 'blue',
                    'action': function () {
                        $.ajax({
                            url: '/admin/adminmgr/blacklist?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {id: id, status: 0},
                            success: function (res) {
                                if(res.code == 200){
                                    window.location.reload();
                                    jobConfirm.toast(res.msg);
                                } else{
                                    jobConfirm.toast(res.msg);
                                }
                            }
                        });
                    }
                },
                '取消': {
                    'class': 'gray',
                    'action': function () {
                    }
                }
            }
        });
    });


});