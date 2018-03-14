$(document).ready(function () {

    $('#form-postjob').submit(function () {

        var params = JSON.stringify({
            'job_name': $('#job_name').val(),
            'place': $('#place').val(),
            'category': parseInt($('#category').val()),
            'number': parseInt($('#number').val()),
            'isschool': parseInt($('input[name="isschool"]:checked').val()),
            'duty': $('#duty').val(),
            'require': $('#require').val()
        });

        //成功地提交
        $.ajax({
            url: "/admin/job-post.html?action=save&_AJAX_=" + Math.random(),
            data: {
                params: params
            },
            type: "post",
            dataType: "json",
            success: function (res) {
                if (res.code == 200) {
                    window.location.href = "/admin/job-index.html";
                } else{
                    UIModals.toast('操作失败');
                }
            }
        });
        return false;
    });
});