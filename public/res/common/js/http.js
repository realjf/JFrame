/**
 * 网络请求封装
 */
var Http = function() {
    // 版本号
    var _version = (new Date()).valueOf();

    /**
     * 补全访问的URL地址
     * @param url
     * @returns {*}
     * @private
     */
    var _url = function(url, cache) {
        if(cache == 'undefined' || cache == false) {
            var re = /\?/;
            if(re.test(url)) {
                url += '&_t=' + _version;
            }else {
                url += '?_t=' + _version;
            }
        }

        return url;
    }

    /**
     * JSON请求
     * @param url
     * @param data
     * @param callback
     */
    var _getJson = function(url, data, callback, options) {
        // shift arguments if data argument was omitted
        if(jQuery.isFunction(data)) {
            callback = callback || data;
            data = undefined;
        }
        _getHtml(url, data, function(result) {
            try {
                result = $.parseJSON(result);
            }catch(e) {
                UIModals.alert(result);
                return;
            }
            if(typeof callback == 'function') {
                callback(result)
            }
        }, options)
    }

    /**
     * 错误提示
     * @param status
     * @private
     */
    var _showError = function(status) {
        if(status == '403') {
            UIModals.alert('没有权限访问')
        }else if(status == '404') {
            UIModals.alert('页面不存在')
        }else if(status == '499') {
            UIModals.alert('执行时间超长，客户端关闭了链接')
        }else if(status.substring(0, 1) == '5') {
            UIModals.alert('程序内部错误')
        }else {
            UIModals.alert('访问出错了,请联系技术');
        }
    }

    /**
     * HTML请求
     * @param url
     * @param data
     * @param callback
     */
    var _getHtml = function(url, data, callback, options) {
        options = $.extend({
            async: true
        }, options || {});
        // shift arguments if data argument was omitted
        if(jQuery.isFunction(data)) {
            callback = callback || data;
            data = undefined;
        }
        $.ajax({
            type: "GET",
            url: _url(url),
            dataType: 'html',
            data: data,
            cache: false,
            async: options.async,
            success: function(data) {
                if(typeof callback == 'function') {
                    callback(data)
                }
            }
        }).fail(function(xhr) {
            _showError(xhr.status);
        }).always(function() {
            // 强制去掉，解决部分浏览器卡死
            $('.loading-mask').remove();
        })
    }
    /**
     * POST请求
     * @param url
     * @param data
     * @param callback
     */
    var _postJson = function(url, data, callback, options) {
        // shift arguments if data argument was omitted
        if(jQuery.isFunction(data)) {
            callback = callback || data;
            data = undefined;
        }
        _postHtml(url, data, function(result) {
            try {
                result = $.parseJSON(result);
            }catch(e) {
                UIModals.alert(result);
                return;
            }
            if(typeof callback == 'function') {
                callback(result)
            }
        }, options)
    }
    /**
     * POST HTML请求
     * @param url
     * @param data
     * @param callback
     */
    var _postHtml = function(url, data, callback, options) {
        options = $.extend({
            async: true
        }, options || {});
        // shift arguments if data argument was omitted
        if(jQuery.isFunction(data)) {
            callback = callback || data;
            data = undefined;
        }
        $.ajax({
            type: "POST",
            url: _url(url),
            data: data,
            dataType: 'html',
            cache: false,
            async: options.async,
            success: function(data) {
                if(typeof callback == 'function') {
                    callback(data)
                }
            }
        }).fail(function(xhr) {
            _showError(xhr.status);
        }).always(function() {
            // 强制去掉，解决部分浏览器卡死
            $('.loading-mask').remove();
        })
    }
    /**
     * 确认请求
     * @param url
     * @param title
     * @param callback
     */
    var _confirm = function(url, title, callback) {
        bootbox.confirm(title, '取消', '确定', function(result) {
            if(!result) return;
            _getJson(url, callback)
        })
    }
    /**
     * POST提交带确认
     * @param url
     * @param data
     * @param title
     * @param callback
     * @private
     */
    var _postConfirm = function(url, data, title, callback) {
        bootbox.confirm(title, '取消', '确定', function(result) {
            if(!result) return;
            _postJson(url, data, callback)
        })
    }
    /**
     * 列表页加载页面/刷新
     * @param element
     * @param url
     * @param data
     * @param callback
     * @private
     */
    var _load = function(element, url, data, callback) {
        // shift arguments if data argument was omitted
        if(jQuery.isFunction(data)) {
            callback = callback || data;
            data = undefined;
        }
        $(element).load(_url(url), data, function(data, status, xhr) {
            if(xhr.status == '403') {
                UIModals.showMsg(element, data)
            }else {
                App.initUniform(element); // initialize uniform elements
                App.fixPageContentHeight(); // 初始化右边高度
            }
            if(typeof callback == 'function') {
                callback(data)
            }
        });
    }
    /**
     * 动态加载js文件
     * @param filename
     * @private
     */
    var _loadJs = function(filename) {
        var fileref = document.createElement('script');
        fileref.setAttribute("type", "text/javascript");
        fileref.setAttribute("src", _url(filename));

        if(typeof fileref != "undefined") {
            document.getElementsByTagName("head")[0].appendChild(fileref);
        }
    }
    /**
     * 动态加载css文件
     * @param filename
     * @private
     */
    var _loadCss = function(filename) {
        var fileref = document.createElement('link');
        fileref.setAttribute("rel", "stylesheet");
        fileref.setAttribute("type", "text/css");
        fileref.setAttribute("href", _url(filename));
        if(typeof fileref != "undefined") {
            document.getElementsByTagName("head")[0].appendChild(fileref);
        }
    }
    return {
        getJson: _getJson,
        postJson: _postJson,
        confirm: _confirm,
        postConfirm: _postConfirm,
        url: _url,
        load: _load,
        getHtml: _getHtml,
        postHtml: _postHtml,
        loadJs: _loadJs,
        loadCss: _loadCss
    }
}()