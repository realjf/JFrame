/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for a single toolbar row.
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

    // The default plugins included in the basic setup define some buttons that
    // are not needed in a basic editor. They are removed here.
    config.removeButtons = 'Cut,Copy,Undo,Redo,Anchor,Strike,Subscript,Superscript';

    // Dialog windows are also simplified.
    config.removeDialogTabs = 'link:advanced';

    config.font_names='宋体/simsun;新宋体/nsimsun;仿宋_GB2312/fangsong_gb2312;楷体_GB2312/kaiti_gb2312;黑体/simhei;微软雅黑/microsoft yahei;'+ config.font_names;

    config.toolbar_Basic = [
        { name: 'font', items: ['Font', 'FontSize'] },
        { name: 'paste', items: ['Paste', 'PasteText']},
        { name: 'text', items: [ 'TextColor', 'BGColor', 'Bold', 'Italic', 'Underline', 'RemoveFormat']},
        { name: 'list', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight','NumberedList', 'BulletedList']},
        { name: 'media', items: ['Smiley', 'Image','Link', 'Unlink' ]},
        { name: 'source', items: ['Source']}
    ];
    config.toolbar = 'Basic';
};
