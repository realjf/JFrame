/**
 * Created by user on 2015/9/9.
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

    $(".edit").live('click', function(){
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.ajax({
            url: '/admin/jobmanage/edit',
            dataType: 'html',
            type: 'get',
            data:{id:id},
            success:function(){

            }
        });
    });

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
                            url: '/admin/jobmanage/delete?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {id: id},
                            success: function (res) {
                                if (res.code == 200) {
                                    window.location.reload();
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

    $(".release").live('click', function () {
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.ajax({
            url: '/admin/jobmanage/release?_AJAX_=' + Math.random(),
            dataType: 'json',
            type: 'post',
            data: {id: id,status:2},
            success: function (res) {
                if(res.code == 200){
                    window.location.reload();
                    jobConfirm.toast('操作成功');
                } else{
                    jobConfirm.toast('操作失败');
                }
            }
        });
    });

    $(".offShelf").live('click', function () {
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        $.ajax({
            url: '/admin/jobmanage/release?_AJAX_=' + Math.random(),
            dataType: 'json',
            type: 'post',
            data: {id: id,status:1},
            success: function (res) {
                if(res.code == 200){
                    window.location.reload();
                    jobConfirm.toast('操作成功');
                } else{
                    jobConfirm.toast('操作失败');
                }
            }
        });
    });

    $("#btn-multi-release").on('click', function(){
        var ids = [];
        $(":checkbox:checked", ".table").each(function(){
            var id = $(this).parents("tr").find("input:checkbox").attr("id");
            if(id){
                ids.push(id);
            }
        });
        $.ajax({
            url: '/admin/jobmanage/multirelease?_AJAX_=' + Math.random(),
            dataType: 'json',
            type: 'post',
            data: {ids:ids.toString(),status:2},
            success: function (res) {
                if(res.code == 200){
                    window.location.reload();
                    jobConfirm.toast('操作成功');
                } else{
                    jobConfirm.toast('操作失败');
                }
            }
        });
    });

    $(".isSchool").live('change', function(){
        var id = $(this).parents("tr").find("input:checkbox").attr("id");
        var status = $(this).val();

        $.ajax({
            url:"/admin/jobmanage/setschool?_AJAX_=" + Math.random(),
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

