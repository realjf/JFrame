/**
 * 弹层
 */
var UIModals = function() {

    /**
     * 打开层
     * @param url
     * @param options
     * @private
     */
    var _open = function(url, options) {
        options = $.extend({
            modalOverflow: false,
            maxHeight: false,
            element: '#ajax-modal',
            pageElement: '',
            container: '',
            callback: ''
        }, options || {});
        options.pageElement = options.pageElement ? options.pageElement : options.element;

        // 定义宽高样式
        $.fn.modal.defaults.modalOverflow = options.modalOverflow;
        if(options.maxHeight) {
            $.fn.modal.defaults.maxHeight = function() {
                return $(window).height() - 165;
            }
        }
        var ajaxModal = $(options.element)
        if(options.container != '') {
            ajaxModal.addClass(options.container);
        }
        // 加载显示层
        $('body').modalmanager('loading');
        Http.load(options.element, url, function(){
            $('body').modalmanager('removeLoading');

            // 初始化分页绑定
            App.initPagination(options.pageElement);
            // 销毁层样式
            ajaxModal.modal().one('destroy', function() {
                if(options.container != '') {
                    ajaxModal.removeClass(options.container);
                }
            });
            if(typeof options.callback == 'function'){
                options.callback(options.element);
            }
            // ESC键关闭层
            //console.log(jQuery.hotkeys)
            if (typeof jQuery.hotkeys != 'undefined') {
                ajaxModal.on("keyup", null, "esc", function(){
                    _close(ajaxModal);
                })
            }
        });
        return ajaxModal;
    }

    /**
     * 打开HTML层
     * @param html
     * @param options
     * @private
     */
    var _openHtml = function(html, options) {
        options = $.extend({
            modalOverflow: false,
            maxHeight: false,
            element: '#ajax-modal',
            pageElement: '',
            container: '',
            pagination: true
        }, options || {});
        options.pageElement = options.pageElement ? options.pageElement : options.element;

        // 定义宽高样式
        $.fn.modal.defaults.modalOverflow = options.modalOverflow;
        if(options.maxHeight) {
            $.fn.modal.defaults.maxHeight = function() {
                return $(window).height() - 165;
            }
        }
        var ajaxModal = $(options.element)
        if(options.container != '') {
            ajaxModal.addClass(options.container);
        }

        $(options.element).html(html);
        if(options.pagination){
            App.initPagination(options.pageElement);
        }
        App.initUniform(options.element); // initialize uniform elements
        App.fixPageContentHeight(); // 初始化右边高度
        // 销毁层样式
        ajaxModal.modal().one('destroy', function() {
            if(options.container != '') {
                ajaxModal.removeClass(options.container);
            }
        });
        // ESC键关闭层
        if (typeof jQuery.hotkeys != 'undefined') {
            ajaxModal.on("keyup", null, "esc", function(){
                _close(ajaxModal);
            })
        }
        return ajaxModal;
    }

    /**
     * 关闭层
     * @param ajaxModal
     * @private
     */
    var _close = function(ajaxModal) {
        ajaxModal.find('button[data-dismiss="modal"]').click();
    }
    /**
     * 提交事件
     * @param ajaxModal
     * @param options
     * @private
     */
    var _submit = function(ajaxModal, options) {
        options = $.extend({
            form: '.ajax-form',
            output: 'json',
            callback: options.callback
        }, options || {});
        ajaxModal.modal('loading');
        ajaxModal.find(options.form).ajaxSubmit({
            //dataType: options.output,
            beforeSerialize:_beforeSerialize,
            success:  function(data) {
                try {
                    if (options.output == 'json' && typeof data == "string") {
                        data = $.parseJSON(data);
                    }
                } catch (e) {
                    _alert(data);
                    return;
                }

                if(options.callback) {
                    options.callback(ajaxModal, data)
                }
            },
            error: function() {
                _alert('保存失败，请重试');
            },
            complete: function() {
                ajaxModal.modal('loading');
                // 强制去掉，解决部分浏览器卡死
                ajaxModal.find('.loading-mask').remove();
            }
        });
    }
    /**
     * 绑定提交事件
     * @param ajaxModal
     * @param options
     * @private
     */
    var _form = function(ajaxModal, options) {
        options = $.extend({
            form: '.ajax-form',
            output: 'json',
            callback: ''
        }, options || {});
        ajaxModal.find(options.form).ajaxForm({
            //dataType: options.output,
            beforeSerialize:function($Form, options){
                ajaxModal.modal('loading');
                _beforeSerialize($Form, options);
            },
            success:  function(data) {
                try {
                    if (options.output == 'json') {
                        data = $.parseJSON(data);
                    }
                } catch (e) {
                    _alert(data);
                    return;
                }

                if(options.callback) {
                    options.callback(ajaxModal, data)
                }
            },
            error: function() {
                _alert('保存失败，请重试');
            },
            complete: function() {
                ajaxModal.modal('loading');
                // 强制去掉，解决部分浏览器卡死
                ajaxModal.find('.loading-mask').remove();
            }
        });
    }
    /**
     * 提交前对数据处理
     * @param $Form
     * @param options
     * @returns {boolean}
     * @private
     */
    var _beforeSerialize = function($Form, options){
        /* 将ckeditor值更新到textarea*/
        if (typeof CKEDITOR != 'undefined') {
            for (var instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
        }
        return true;
    }
    /**
     * 简单的表单保存回调
     * @param ajaxModal
     * @param data
     * @private
     */
    var _callback = function(ajaxModal, data) {
        // 屏幕向上滚动
        ajaxModal.animate({scrollTop: 0}, 'slow');
        var css = (data.code == 100) ? 'alert-info' : 'alert-error';
        var sec = (data.code == 100) ? 5000 : 50000;
        var $modalBody = ajaxModal.find('.modal-body');
        $modalBody.find('.alert').remove();
        $modalBody.prepend('<div class="alert ' + css + ' hide">' + data.message + '<button type="button"  class="close" data-dismiss="alert"></button></div>')
        $modalBody.find('.alert').fadeIn(2000).fadeOut(sec);
    }
    /**
     * 简单提示信息
     * @param message
     * @param data
     * @param callback
     * @private
     */
    var _alert = function(message, data, callback) {
        // shift arguments if data argument was omitted
        var _default = '确定';
        if ( jQuery.isFunction( data ) ) {
            callback = callback || data;
            data = _default;
        } else if (typeof data == 'undefined') {
            data = _default;
        }
        bootbox.alert(message, data, callback);
    }

    /**
     * 浮动提示信息
     * @param code
     * @param message
     * @private
     */
    var _tips = function(code, message) {
        var css = (code == 100) ? 'alert-info' : 'alert-error';
        var $body = $('body');
        $body.find('.alert.float').remove();
        $body.prepend('<div class="alert float ' + css + ' hide">' + message + '</div>');
        $body.find('.alert.float').fadeIn(1000).fadeOut(5000);
    }

    /**
     * 弹层页面内容刷新
     * @param element
     * @param url
     * @param data
     * @param callback
     * @private
     */
    var _replace = function(element, url, data, callback) {
        // shift arguments if data argument was omitted
        if ( jQuery.isFunction( data ) ) {
            callback = callback || data;
            data = undefined;
        }
        $(element).load(Http.url(url), data, function(data, status, xhr){
            if (xhr.status == '403') {
                UIModals.showMsg(element, data)
            } else {
                App.initUniform(element); // initialize uniform elements
            }
            if (typeof callback == 'function') {
                callback(data)
            }
        });
    }

    /**
     * 403等提示信息
     * @param element
     * @param msg
     * @private
     */
    var _showMsg = function(element, msg) {
        var html = '' +
            '<div class="modal-header">' +
               '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&nbsp;</button>' +
               '<h3>提示信息</h3>' +
            '</div>' +
            '<div class="modal-body">' + msg + '</div>' +
            '<div class="modal-footer">' +
                '<button type="button" data-dismiss="modal"  class="btn">确定</button>' +
            '</div>';
        $(element).attr('class', 'modal hide fade in');
        $(element).html(html);
    }
    return {
        open: _open,
        openHtml: _openHtml,
        close: _close,
        submit: _submit,
        form: _form,
        tips: _tips,
        alert: _alert,
        replace: _replace,
        showMsg: _showMsg,
        callback: _callback
    }
}()
