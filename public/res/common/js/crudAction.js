/**
 * 简单的后台操作交互事件
 *
 * @type {string}
 */
var crudAction = function() {
    var baseUrl = '';
    var reloadData = function(data, e) {
        var tData = $(e).data();
        if(tData.refresh == true) {
            window.location.reload();
        }else if(tData.reload != false) {
            if(tData.reloadbase !== false) {
                Http.load('#subContent', baseUrl);
            }
            var options = {};
            if(tData.reload_layer !== 'current') {
                var ed = $(e).parents('.reload_model').data();
                if(ed && ed.parentElement && ed.parentUrl && ed.parentElement != '#subContent') {
                    options.element = ed.parentElement;
                    UIModals.open(ed.parentUrl, options);
                }
            }else {
                if(!tData.element) {
                    var ed = $(e).parents('.reload_model').data();
                }else {
                    ed = tData;
                }
                if(ed && ed.element && ed.element != '#subContent') {
                    if (!ed.url) { // 二级弹层
                        divData = $(ed.element).data();
                        if (divData && divData.element && divData.parentUrl && divData.parentElement != "#subContent" ) {
                            options.element = divData.parentElement;
                            //console.log(divData)
                            UIModals.open(divData.parentUrl, options);
                        }
                    } else {
                        options.element = ed.element;
                        UIModals.open(ed.url, options);
                    }
                }
            }
        }
    };
    var _submitDo = function() {
        $(document).on('click', '.ajax-form .submitDo', function() {
            var _this = $(this);
            var el = $(this).data();
            var options = {element: '#ajax-modal'};
            if(el.element) {
                options.element = el.element;
            }

            var callback = function(ajaxModal, data) {

                UIModals.tips(data.code, data.message);
                if(data.code == 100) {
                    if(el.close != false) {
                        UIModals.close(ajaxModal);
                    }
                    reloadData(data, _this);
                } else if(data.code == 302) {
                   window.location.href= data.data
                }
            };
            var commit = true;
            var tipMsg = '必填项不能空';
            //console.log(options.element)
            $(options.element).find(':text.request').each(function() {
                if($(this).val() == '') {
                    commit = false;
                }
            });
            if(commit) {
                UIModals.submit($(options.element), {callback: callback})
            }else {
                UIModals.tips(0, tipMsg);
            }
        });
    };
    var _ajaxDo = function() {
        $(document).on('click', '.ajaxDo', function() {
            var _this = $(this);
            var el = $(this).data();
            var callBack = function(data) {
                var options = {};
                if(el.element) {
                    options.element = el.element;
                }
                UIModals.tips(data.code, data.message);

                if(data.code == 100) {
                    reloadData(data, _this);
                }
            };
            var data = {};
            var title = $(this).attr('title');
            if(el.confirm !== false && title) {
                Http.confirm(el.href, title, callBack);
            }else {
                Http.getJson(el.href, data, callBack);
            }
        });
    };
    var _dialogDo = function() {
        $(document).on('click', '.dialogDo', function() {
            var _this = $(this);
            var el = $(this).data();
            var options = {
                element: '#ajax-modal', callback: function(element) {
                    var _element = $(element);
                    var pl = $(_this).parents('.reload_model').data();
                    _element.addClass('reload_model');
                    _element.data('parentUrl', pl ? pl.url : baseUrl);
                    _element.data('parentElement', pl ? pl.element : '#subContent');
                    _element.data('url', el.href);
                    _element.data('element', element);

                }
            };
            if(el.container) {
                options.container = el.container;
            }
            if(el.element) {
                options.element = el.element;
            }
            UIModals.open(el.href, options);
        });
    };
    var _searchDo = function() {
        $(document).on('click', '.searchDo', function() { //modal
            var _this = $(this);
            var _currentModel = _this.closest('.reload_model');
            var pl = _currentModel.data();
            var reg = /\?/i;
            var re = new RegExp(reg);
            var params_array = _this.closest('form').serializeArray();
            var current_url = pl.url;
            var url_split = current_url.split(/[\\?&]/);
            var jsonObj = {};
            var eq_reg = new RegExp("=");
            for(var i = 0; i <= url_split.length; i++) {
                if(eq_reg.test(url_split[i])) {
                    var key = url_split[i].split(eq_reg);
                    jsonObj[key[0]] = key[1];
                }
            }
            $.each(params_array, function(p, field) {
                jsonObj[field.name] = field.value;
            });
            if(!$.isEmptyObject(jsonObj)) {
                var formatJsonObj = {};
                $.each(jsonObj, function(fieldName, value) {
                    var name = decodeURI(fieldName);
                    formatJsonObj[name] = value;
                });
                if(re.test(current_url)) {
                    var idx = current_url.indexOf('?');
                    var current_url_prefix = current_url.substring(0, idx);
                }else {
                    current_url_prefix = current_url;
                }
                var params = $.param(formatJsonObj);
                current_url = current_url_prefix + '?' + params;
            }
            _this.closest('.reload_model').data('url', current_url);
            Http.load('#' + _currentModel.attr('id'), current_url);
            return false;
        });
    };

    return {
        init: function() {
            _submitDo();
            _ajaxDo();
            _dialogDo();
            _searchDo();
            baseUrl = window.location.href;
        }
    }
}();
var crudActionRun = function() {
    /**
     * 弹层中刷新当前窗体上一次请求的url
     * @param  jquerythis //传当前窗体的jquery对象
     * @private
     */
    var _reloadPageDo = function(jquerythis) {
        var _currentModel = jquerythis.closest('.reload_model');
        var pl = _currentModel.data();
        var current_url = pl.url;
        Http.load('#' + _currentModel.attr('id'), current_url);
    };
    return {
        reloadPageDo: _reloadPageDo
    }
}();
$(document).ready(function() {
    crudAction.init();
});
