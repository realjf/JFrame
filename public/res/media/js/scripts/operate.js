(function($){
    var operate = {
        modal :'',
        flushUrl :'',
        flushDom : '',
        __callback : '',
        edit:function (e) {
            var href = $(e).attr('in-href');
            $('body').modalmanager('loading');
            operate.modal.load(href, '', function() {
                operate.modal.modal();
            });
        },

        btnSub:function(){
            operate.modal.modal('loading');
            var $ajaxForm = operate.modal.find('.ajax-form');
            $ajaxForm.ajaxForm({
                success:function(data){
                    operate.modal.modal('loading');
                    try {
                        data = $.parseJSON(data);
                    } catch (e) {
                        UIModals.alert(data);
                        return;
                    }
                    var css = data.code != '100' ? 'alert-error' : 'alert-info';
                    var $modelBody = operate.modal.find('.modal-body');
                    var message = data.message;
                    if(data.result.tips){
                        message = data.result.tips;
                    }
                    $modelBody.find('.alert').hide();
                    $modelBody.prepend('<div class="alert ' + css + ' fade in">'+message+'<button type="button"  class="close" data-dismiss="alert"></button></div>');
                    if(data.code == 100){
                        if(operate.__callback && (typeof  operate.__callback == 'function') ){
                            operate.__callback(data);
                        }
                        if ($ajaxForm.attr('auto-close') == 1) {
                            operate.modal.find('.close').click();
                            UIModals.tips(data.code, data.message);
                        }
                        operate.flush();
                    }
                }
            });
        },
        ajaxDo : function (e) {
            var href = $(e).attr('in-href');
            var tips = $(e).attr('title');
            tips = "确定"+tips+"吗?";
            Http.postConfirm(href,{},tips, function(data) {
                var message = data.message;
                if(data.result.tips){
                    message = data.result.tips;
                }
                UIModals.tips(data.code, message);
                if(data.code == 100){
                    operate.flush();
                }
            });
        },
        flush : function(){
            if(operate.flushDom && operate.flushUrl){
                Http.getHtml(operate.flushUrl, function(html) {
                        $(html).replaceAll(operate.flushDom);
                        App.fixPageContentHeight();
                    }
                )
            }else{
               // alert('未定义');
            }
        },
        /*
          -------自定义属性值
          遮罩层节点： #ajax-modal
          刷新请求内容地址： /ios/wap/home.html
          刷新节点：#wap_home
          提交表单回调：__callback
          --------------配置属性值
          编辑按钮： .modal-edit
         ajax 表单：.ajax-form
          ajax提交表单按钮： .ajax-form-save
          ajax操作按钮：ajax-do
         */
        init:function(dom,flushUrl,flushDom,__callback){
            if(dom){
                operate.modal = $(dom);
            }else{
                operate.modal = $('#ajax-modal');
            }
            if(__callback){
                operate.__callback = __callback;
            }
            if(flushDom && flushUrl){
                operate.flushUrl = flushUrl;
                operate.flushDom = flushDom;
            }
            $(".modal-edit").die().live('click',function(){
                operate.edit(this);
            });
            $(".ajax-form-save").die().live('click',function(){
                operate.btnSub();
                $('.loading-mask').remove();
            });
            $('.ajax-do').die().live('click',function(){
                operate.ajaxDo(this);
            });
        }
    };
    $.extend({
        operate:operate
    });
})(window.jQuery);
