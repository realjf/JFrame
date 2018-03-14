var row = {
    addBase: function(tr) {
        var newTr = tr.clone().insertAfter(tr).removeClass('tr_eg');
        newTr.css("display", "")
        newTr.find('td:not(:last)').children('input[type="text"]').each(function() {
            var value = $(this).attr('default') || '';
            $(this).val(value);
        });
        newTr.find('td:not(:last)').children('select').each(function() {
            if ( $(this).attr('default') != undefined ) {
                $(this).val($(this).attr('default'));
            }
        });
        newTr.find('td:not(:last)').children('textarea').css({'width': '95%', 'height': 'auto'}).val('');
    },
    add: function(obj) {
        var tr = $(obj).parent().parent();
        row.addBase(tr)
    },
    addFirst: function(obj) {
        var tr = $(obj).parents('table').find('.tr_eg');
        row.addBase(tr)
    },
    up: function(obj) {
        var tr = $(obj).parent().parent();
        var preTr = tr.prev();
        if(tr.prev().length == 0) {
            alert("已经到顶啦")
            return false;
        }
        tr.prev().replaceWith(tr.clone());
        tr.replaceWith(preTr.clone());
        return true;
    },
    down: function(obj) {
        var tr = $(obj).parent().parent();
        var nextTr = tr.next();
        if(nextTr.length == 0) {
            alert("已经到底啦")
            return false;
        }
        tr.next().replaceWith(tr.clone());
        tr.replaceWith(nextTr.clone());
        return true;
    },
    remove: function(obj) {
        if($(obj).parent().parent().attr('class') == 'tr_eg') {
            alert("模板行不能删除")
            return false;
        }
        $(obj).parent().parent().remove();
    }
}
$.extend({
    row: {
        add: row.add,
        addFirst: row.addFirst,
        up: row.up,
        down: row.down,
        remove: row.remove
    }
})
