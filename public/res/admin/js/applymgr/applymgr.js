/**
 * Created by user on 2015/9/10.
 */
$(document).ready(function(){
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
        var id = $(this).parents("tr").find("input:text").attr("id");
        var uid = $(this).parents("tr").find("input:hidden").attr("id");
        var job_id = $(this).parents("tr").find("input:hidden").eq(1).attr("id");
        var resume_id = $(this).parents("tr").find("input:hidden").eq(2).attr("id");
        $.confirm({
            'title': '提示',
            'message': '确定删除该记录吗？',
            'buttons': {
                '确定': {
                    'class': 'blue',
                    'action': function () {
                        $.ajax({
                            url: '/admin/applymgr/delete?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {id: id,uid:uid,job_id:job_id,resume_id:resume_id},
                            success: function (res) {
                                if(res.code == 200){
                                    window.location.reload();
                                    jobConfirm.toast('操作成功');
                                } else{
                                    jobConfirm.toast('操作失败');
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



    $(".resumeStatus").live('change', function(){
        var id = $(this).parents("tr").find("input:text").attr("id");
        var status = $(this).val();

        $.ajax({
            url:"/admin/applymgr/setstatus?_AJAX_=" + Math.random(),
            dataType:'json',
            type:'post',
            data:{id:id,status:status},
            success:function(res){
                if(res.code == 200){
                    jobConfirm.toast('操作成功');
                } else{
                    jobConfirm.toast('操作失败');
                }
            }
        });
    });

});