/**
 * Created by user on 2015/9/25.
 */

var Modal = function(){

    var _jobEdit = function(id){
        $.ajax({
            url: '/admin/jobmanage/edit?_AJAX_='+Math.random(),
            dataType: 'json',
            type: 'get',
            data:{id:id},
            success:function(){

            }
        })
    }

    return {
        jobEdit: _jobEdit()
    }
}()
