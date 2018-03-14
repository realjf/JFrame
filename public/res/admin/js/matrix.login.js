
$(document).ready(function(){

	var login = $('#loginform');
	var recover = $('#recoverform');
	var speed = 400;

	$('#to-recover').click(function(){
		
		$("#loginform").slideUp();
		$("#recoverform").fadeIn();
	});
	$('#to-login').click(function(){
		
		$("#recoverform").hide();
		$("#loginform").fadeIn();
	});
	
	
	$('#login').click(function(){
        //$.ajax({
        //    url:"/Admin/index/login",
        //    type:"post",
        //    data:{
        //        username:$("#username").val(),
        //        password:$("#password").val()
        //    },
        //    dataType:"json",
        //    success:function(res){
        //        window.location.href = "/Admin/manage/index";
        //    },
        //    error:function(e){
        //        $("#password").attr("value", "");
        //    }
        //});
	});
    
    if($.browser.msie == true && $.browser.version.slice(0,3) < 10) {
        $('input[placeholder]').each(function(){ 
       
        var input = $(this);       
       
        $(input).val(input.attr('placeholder'));
               
        $(input).focus(function(){
             if (input.val() == input.attr('placeholder')) {
                 input.val('');
             }
        });
       
        $(input).blur(function(){
            if (input.val() == '' || input.val() == input.attr('placeholder')) {
                input.val(input.attr('placeholder'));
            }
        });
    });

        
        
    }
});