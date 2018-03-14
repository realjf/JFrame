/**
 * Created by user on 2015/9/12.
 */
$(document).ready(function () {

    $(".remove").live('click', function () {
        var uid = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title': '提示',
            'message': '确定删除该记录吗？',
            'buttons': {
                '确定': {
                    'class': 'blue',
                    'action': function () {
                        $.ajax({
                            url: '/admin/usermgr/delete?_AJAX_=' + Math.random(),
                            dataType: 'json',
                            type: 'post',
                            data: {uid: uid},
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

    $(".blacklist").live('click', function(){
        var uid = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title':'提示',
            'message':'确定将该用户加入黑名单吗？',
            'buttons':{
                '确定':{
                    'class':'blue',
                    'action':function(){
                        $.ajax({
                            url:'/admin/usermgr/blacklist?_AJAX_=' + Math.random(),
                            dataType:'json',
                            type:'post',
                            data:{uid:uid,status:1},
                            success:function(res){
                                if(res.code == 200){
                                    window.location.reload();
                                }
                            }
                        });
                    }
                },
                '取消':{
                    'class':'gray',
                    'action':function(){}
                }
            }
        });
    });

    $(".deblacklist").live('click', function(){
        var uid = $(this).parents("tr").find("input:checkbox").attr("id");
        $.confirm({
            'title':'提示',
            'message':'确定将该用户从黑名单中移除吗？',
            'buttons':{
                '确定':{
                    'class':'blue',
                    'action':function(){
                        $.ajax({
                            url:'/admin/usermgr/blacklist?_AJAX_=' + Math.random(),
                            dataType:'json',
                            type:'post',
                            data:{uid:uid,status:0},
                            success:function(res){
                                if(res.code == 200){
                                    window.location.reload();
                                }
                            }
                        });
                    }
                },
                '取消':{
                    'class':'gray',
                    'action':function(){}
                }
            }
        });
    });
});