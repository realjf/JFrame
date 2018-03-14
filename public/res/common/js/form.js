/**
 * 表单封装
 */
var Form = function () {
    var isRTL = false;
    /**
     * ajax下拉单,默认值通过属性value和data-text设置
     * @param element
     * @param url
     * @private
     */
    var _select = function (element, url) {
        $(element).select2({
            placeholder: "搜索",
            minimumInputLength: 1,
            formatNoMatches: function () {
                return "没有内容相匹配";
            },
            formatInputTooShort: function (input, min) {
                var n = min - input.length;
                return "至少输入 " + n + " 个字";
            },
            formatSelectionTooBig: function (limit) {
                return "您只能选择 " + limit + " 个项";
            },
            formatLoadMore: function (pageNumber) {
                return "正在加载更多结果...";
            },
            formatSearching: function () {
                return "正在搜索...";
            },
            ajax: {
                url: Http.url(url),
                dataType: 'json',
                cache: false,
                data: function (term, page) {
                    return {
                        q: term
                    };
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            },
            initSelection: function (element, callback) {
                var data = {id: element.val(), text: element.attr('data-text')};
                callback(data);
            }
        });
    }

    /**
     * 表单验证
     * @private
     */
    var _validate = function () {
        $('form.validate').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            invalidHandler: function (event, validator) { //display error alert on jquery-form submit
                $('.alert-error').show();
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.control-group').addClass('has-error'); // set error class to the control group
            },

            success: function (label) {
                label.closest('.control-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function (error, element) {
                error.insertAfter(element.closest('.controls'));
            },

            submitHandler: function (form) {
                UIModals.submit($(form), {callback: UIModals.callback});
            }
        });
    }


    /**
     * 日历区间选择
     * @param element
     * @param options
     * @private
     */
    var _daterangepicker = function (element,options) {
         options = $.extend({
            minDate: -365,
            maxDate: +365,
        }, options || {});
        var startDate = $(element).siblings('input.start').val()
        var endDate = $(element).siblings('input.end').val()
        if (!startDate) {
            startDate = Date.today().add({days: -29})
        }
        if (!endDate) {
            endDate = Date.today();
        }
        $(element).daterangepicker({
            ranges: {
                '今天': ['today', 'today'],
                '昨天': ['yesterday', 'yesterday'],
                '最近7天': [Date.today().add({
                    days: -6
                }), 'today'],
                '本周': [Date.today().moveToDayOfWeek(1, -1), Date.today().moveToDayOfWeek(0)],
                '上周': [Date.today().add({days: -7}).moveToDayOfWeek(1, -1), Date.today().moveToDayOfWeek(0, -1)],
                '最近29天': [Date.today().add({
                    days: -29
                }), 'today'],
                '本月': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                '上月': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: (isRTL ? 'left' : 'right'),
            format: 'yyyy-MM-dd',
            separator: ' 到 ',
            startDate: startDate,
            endDate: endDate,
            minDate: Date.today().add({
                days: options.minDate
            }),
            maxDate: Date.today().add({
                days: options.maxDate
            }),
            locale: {
                applyLabel: '确定',
                fromLabel: '开始',
                toLabel: '结束',
                customRangeLabel: '自定义',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        }, function (start, end) {
            $(element + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
            $(element).siblings('input.start').val(start.toString('yyyy-MM-dd'))
            $(element).siblings('input.end').val(end.toString('yyyy-MM-dd'))
        });
    };

    /**
     * 日历区间选择，一个页面多个插件
     * @param element
     * @param id
     * @param options
     * @private
     */
    var _daterangeidpicker = function (element,id,options) {
        options = $.extend({
            minDate: -365,
            maxDate: +365
        }, options || {});
        var startId = id + '_start',
            endId = id + '_end';
        var startDate = $(element).siblings('input.' + startId).val();
        var endDate = $(element).siblings('input.' + endId).val();
        if (!startDate) {
            startDate = Date.today().add({days: -29})
        }
        if (!endDate) {
            endDate = Date.today();
        }
        $(element).daterangepicker({
            ranges: {
                '今天': ['today', 'today'],
                '昨天': ['yesterday', 'yesterday'],
                '最近7天': [Date.today().add({
                    days: -6
                }), 'today'],
                '本周': [Date.today().moveToDayOfWeek(1, -1), Date.today().moveToDayOfWeek(0)],
                '上周': [Date.today().add({days: -7}).moveToDayOfWeek(1, -1), Date.today().moveToDayOfWeek(0, -1)],
                '最近29天': [Date.today().add({
                    days: -29
                }), 'today'],
                '本月': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                '上月': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: (isRTL ? 'left' : 'right'),
            format: 'yyyy-MM-dd',
            separator: ' 到 ',
            startDate: startDate,
            endDate: endDate,
            minDate: Date.today().add({
                days: options.minDate
            }),
            maxDate: Date.today().add({
                days: options.maxDate
            }),
            locale: {
                applyLabel: '确定',
                fromLabel: '开始',
                toLabel: '结束',
                customRangeLabel: '自定义',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        }, function (start, end) {
            $(element + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
            $(element).siblings('input.' + startId).val(start.toString('yyyy-MM-dd'));
            $(element).siblings('input.' + endId).val(end.toString('yyyy-MM-dd'))
        });
    };

    /**
     * 日历区间选择，快速选项为当前时间之后
     * @param element
     * @param options
     * @private
     */
    var _futurerangepicker = function (element,options) {
        options = $.extend({
            minDate: -365,
            maxDate: +365,
        }, options || {});
        var startDate = $(element).siblings('input.start').val()
        var endDate = $(element).siblings('input.end').val()
        if (!startDate) {
            startDate = Date.today().add({days: -29})
        }
        if (!endDate) {
            endDate = Date.today();
        }
        $(element).daterangepicker({
            ranges: {
                '今天': ['today', 'today'],
                '明天': ['tomorrow', 'tomorrow'],
                '未来7天': ['today', Date.today().add({
                    days: +6
                })],
                '本周': [Date.today().moveToDayOfWeek(0, -1), Date.today().moveToDayOfWeek(0)],
                '下周': [Date.today().moveToDayOfWeek(0, -1), Date.today().add({days: +7}).moveToDayOfWeek(1, -1)],
                '最近29天': [Date.today().add({
                    days: -29
                }), 'today'],
                '本月': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                '上月': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: (isRTL ? 'left' : 'right'),
            format: 'yyyy-MM-dd',
            separator: ' 到 ',
            startDate: startDate,
            endDate: endDate,
            minDate: Date.today().add({
                days: options.minDate
            }),
            maxDate: Date.today().add({
                days: options.maxDate
            }),
            locale: {
                applyLabel: '确定',
                fromLabel: '开始',
                toLabel: '结束',
                customRangeLabel: '自定义',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        }, function (start, end) {
            $(element + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
            $(element).siblings('input.start').val(start.toString('yyyy-MM-dd'))
            $(element).siblings('input.end').val(end.toString('yyyy-MM-dd'))
        });
    }

    var _rangepicker = function (element) {
        var startDate = $(element).siblings('input.start').val();
        var endDate = $(element).siblings('input.end').val();
        if (!startDate) {
            startDate = Date.today().add({days: -29})
        }
        if (!endDate) {
            endDate = Date.today();
        }
        $(element).daterangepicker({
            opens: (isRTL ? 'left' : 'right'),
            format: 'yyyy-MM-dd',
            separator: ' 到 ',
            startDate: startDate,
            endDate: endDate,
            minDate: Date.today().add({
                days: -365
            }),
            maxDate: Date.today().add({
                days: +365
            }),
            locale: {
                applyLabel: '确定',
                fromLabel: '开始',
                toLabel: '结束',
                customRangeLabel: '自定义',
                daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                firstDay: 1
            }
        }, function (start, end) {
            $(element + ' span').html(start.toString('yyyy-MM-dd') + ' - ' + end.toString('yyyy-MM-dd'));
            $(element).siblings('input.start').val(start.toString('yyyy-MM-dd'));
            $(element).siblings('input.end').val(end.toString('yyyy-MM-dd'))
        })
    }
    var _datetimepicker = function (element) {
        $(element).datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd hh:ii',
            clearBtn: true,
            minute: 10
        });
    }
    var _datepicker = function (element) {
        $(element).datepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            clearBtn: true,
            minute: 10
        });
    }
    var _clockface = function (element) {
        $(element).clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });
        $(element + '_toggle').click(function (e) {
            e.stopPropagation();
            $(element).clockface('toggle');
        });

        $(element + '_modal').clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });

        $(element + '_modal_toggle').click(function (e) {
            e.stopPropagation();
            $(element + '_modal').clockface('toggle');
        });
    };
    var _ajaxForm = function () {
        $('.ajax-form .submit').bind('click', function () {
            var _this = $(this);
            var form = _this.parents('form')[0];
            var $modal = $(form).parents('.modal');
            var callback = function ($ajaxModal, data) {
                if (data.code == 100) {
                    UIModals.close($ajaxModal);
                    if (typeof _this.attr('data-noreload') == 'undefined') {
                        UIModals.alert(data.message, function () {
                            window.location.reload();
                        });
                    } else {
                        UIModals.alert(data.message);
                    }
                    return;
                }
                var css = data.code != '100' ? 'alert-error' : 'alert-info';
                var $modalBody = $ajaxModal.find('.modal-body');
                $modalBody.find('.alert').hide();
                $modalBody.prepend('<div class="alert ' + css + ' fade in">' + data.message + '<button type="button"  class="close" data-dismiss="alert"></button></div>');
            };
            var title = $(this).attr('title');
            if (title) {
                bootbox.confirm(title, '取消', '确定', function (result) {
                    if (!result) {
                        return;
                    }
                    UIModals.submit($modal, {form: form, callback: callback})
                });
            } else {
                UIModals.submit($modal, {form: form, callback: callback});
            }
            return false;
        })
    };
    return {
        select: _select,
        validate: _validate,
        daterangepicker: _daterangepicker,
        daterangeidpicker: _daterangeidpicker,
        futurerangepicker: _futurerangepicker,
        rangepicker: _rangepicker,
        datetimepicker: _datetimepicker,
        datepicker: _datepicker,
        clockface: _clockface,
        ajaxform: _ajaxForm
    }
}()