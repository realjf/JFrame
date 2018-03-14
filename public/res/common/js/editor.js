/**
 * 编辑器
 */
var Editor = function(){
    /**
     * 检查浏览器版本
     * @private
     */
    var _checkAgent = function() {
        if ( !CKEDITOR.env.isCompatible ) {
            UIModals.tips(99, "重要提示：浏览器与在线编辑器不兼容");
            throw new Error( 'The environment is incompatible.' );
        }
    };

    /**
     * 基本编辑器
     * @param id
     * @private
     */
    var _basic = function (id) {
        CKEDITOR.replace(id, {
            enterMode : CKEDITOR.ENTER_BR,
            allowedContent : true,
            customConfig: Http.url('/res/common/js/editor_config.js')
        });
    };

    var _huodong = function(id){
        var config = CKEDITOR.config;
        config.toolbarGroups = [
            { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
            { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        ];

        config.font_names ='宋体/simsun;新宋体/nsimsun;仿宋_GB2312/fangsong_gb2312;楷体_GB2312/kaiti_gb2312;黑体/simhei;微软雅黑/microsoft yahei;' + config.font_names;

        config.toolbar_Basic = [
            { name: 'font', items: ['Font', 'FontSize'] },
            { name: 'paste', items: ['Paste', 'PasteText']},
            { name: 'text', items: [ 'TextColor', 'BGColor', 'Bold', 'Italic', 'Underline', 'RemoveFormat']},
            { name: 'list', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight','NumberedList', 'BulletedList']},
            { name: 'media', items: ['Image','Link', 'Unlink' ]},
            { name: 'source', items: ['Source']},
            { name: 'huodong', items: ['Undo', 'Redo', 'Templates', 'Preview', 'Maximize']}
        ];
        config.toolbar = 'Basic';
        config.templates_files = [CKEDITOR.getUrl(CKEDITOR.plugins.getPath('templates') + 'templates/huodong.js')];
        config.templates = 'huodong';
        config.enterMode = CKEDITOR.ENTER_BR;
        config.allowedContent = true;
        config.forcePasteAsPlainText =false;//强制去除复制的格式
        config.contentsCss = CKEDITOR.getUrl(CKEDITOR.plugins.getPath('templates') + 'templates/css/huodong.css');
        CKEDITOR.replace(id, config);
    };
    var _message = function (id) {
        var config = CKEDITOR.config;

        config.font_defaultLabel='宋体';
        config.fontSize_defaultLabel='14px';

        config.toolbar_Basic = [
            { name: 'text', items: [ 'TextColor', 'Bold']},
            { name: 'media', items: ['Link', 'Unlink']},
        ];
        config.toolbar = 'Basic';
        config.enterMode = CKEDITOR.ENTER_BR;
        CKEDITOR.replace(id, config);
        // 默认新窗口打开
        CKEDITOR.on( 'dialogDefinition', function( ev )
        {
            var dialogName = ev.data.name;
            var dialogDefinition = ev.data.definition;
            if ( dialogName == 'link' )
            {
                var targetTab = dialogDefinition.getContents( 'target' );
                var targetField = targetTab.get('linkTargetType');
                targetField['default'] = '_blank';
            }
        });
    };

    var _news = function (id) {
        var config = CKEDITOR.config;
        config.toolbarGroups = [
            { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
            { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
            { name: 'forms' },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
            { name: 'links' },
            { name: 'insert' },
            { name: 'styles' },
            { name: 'colors' },
            { name: 'tools' },
            { name: 'others' },
            { name: 'about' }
        ];

        config.removeButtons = 'Cut,Copy,Undo,Redo,Anchor,Strike,Subscript,Superscript';

        config.removeDialogTabs = 'link:advanced';

        config.font_names='宋体/simsun;新宋体/nsimsun;仿宋_GB2312/fangsong_gb2312;楷体_GB2312/kaiti_gb2312;黑体/simhei;微软雅黑/microsoft yahei;'+ config.font_names;

        config.toolbar_Basic = [
            { name: 'font', items: ['Font', 'FontSize'] },
            { name: 'paste', items: ['Paste', 'PasteText']},
            { name: 'text', items: [ 'TextColor', 'BGColor', 'Bold', 'Italic', 'Underline', 'RemoveFormat']},
            { name: 'list', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight','NumberedList', 'BulletedList']},
            { name: 'media', items: ['Smiley', 'Image','Link', 'Unlink','CreateDiv','Flash']},
            { name: 'source', items: ['Source']}
        ];

        config.toolbar = 'Basic';
        config.width = 1500;
        config.height = 600;
        CKEDITOR.replace(id, config);
        // 默认新窗口打开
        CKEDITOR.on( 'dialogDefinition', function( ev )
        {
            var dialogName = ev.data.name;
            var dialogDefinition = ev.data.definition;
            if ( dialogName == 'link' )
            {
                var targetTab = dialogDefinition.getContents( 'target' );
                var targetField = targetTab.get('linkTargetType');
                targetField['default'] = '_blank';
            }
        });
    };

    return {
        basic: function(id) {
            _checkAgent();
            _basic(id)
        },
        huodong: function(id){
            _checkAgent();
            _huodong(id);
        },
        message: function(id){
            _checkAgent();
            _message(id);
        },
        news: function(id){
            _checkAgent();
            _news(id);
        }
    }
}();