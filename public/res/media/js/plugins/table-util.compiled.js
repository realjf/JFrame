/**
 * Created by zjj on 17-3-9.
 */

'use strict';

function _inherits(subClass, superClass) { if (typeof superClass !== 'function' && superClass !== null) { throw new TypeError('Super expression must either be null or a function, not ' + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError('Cannot call a class as a function'); } }

var TableUtil = (function () {
    var Storage = (function () {
        function Storage() {
            _classCallCheck(this, Storage);
        }

        Storage.prototype.set = function set(key, value, expires) {
            throw new Error('abstract');
        };

        Storage.prototype.get = function get(key) {
            var defaultValue = arguments.length <= 1 || arguments[1] === undefined ? null : arguments[1];

            throw new Error('abstract');
        };

        Storage.prototype.del = function del(key) {
            this.set(key, null, -1);
        };

        return Storage;
    })();

    var Cookie = (function (_Storage) {
        _inherits(Cookie, _Storage);

        function Cookie() {
            _classCallCheck(this, Cookie);

            _Storage.apply(this, arguments);
        }

        Cookie.prototype.set = function set(key, value, expires) {
            var d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            if (expires) {
                document.cookie = key + '=' + JSON.stringify(value) + ';path=/;expires=' + d.toGMTString();
            } else {
                document.cookie = key + '=' + JSON.stringify(value) + ';path=/';
            }
        };

        Cookie.prototype.get = function get(key) {
            var defaultValue = arguments.length <= 1 || arguments[1] === undefined ? null : arguments[1];

            var a = undefined,
                reg = new RegExp('(^| )' + key + '=([^;]*)(;|$)');
            try {
                return (a = document.cookie.match(reg)) ? JSON.parse(unescape(a[2])) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        };

        return Cookie;
    })(Storage);

    var LocalStorage = (function (_Storage2) {
        _inherits(LocalStorage, _Storage2);

        function LocalStorage() {
            _classCallCheck(this, LocalStorage);

            _Storage2.apply(this, arguments);
        }

        LocalStorage.prototype.set = function set(key, value, expires) {
            var expired_at = new Date().getTime() + expires * 1000;
            window.localStorage.setItem(key, JSON.stringify({ value: value, expired_at: expired_at }));
        };

        LocalStorage.prototype.get = function get(key) {
            var defaultValue = arguments.length <= 1 || arguments[1] === undefined ? null : arguments[1];

            var jsonstr = window.localStorage.getItem(key);
            try {
                var obj = JSON.parse(jsonstr);
                if (obj.expired_at <= new Date().getTime()) {
                    return obj.value;
                }
            } catch (e) {
                console.log(e);
            }
            return defaultValue;
        };

        return LocalStorage;
    })(Storage);

    var Table = (function () {
        function Table(seltor) {
            _classCallCheck(this, Table);

            this.model = $(seltor);
            this.model.length > 0 && (this.model = $(this.model.get(0)));
            this.mode = 'show';
            this.hideIndexs = [];
            if (this.model.length === 0) {
                this.mode = 'disabled';
                return;
            }
            this.init();
        }

        Table.prototype.init = function init() {
            if (this.mode === 'disabled') return null;
            this.columnsLen = this.thCount();
            this.columns = [];
            for (var i = 1; i <= this.columnsLen; ++i) {
                this.columns[i] = this.model.find('thead>tr>*:nth-child(' + i + '),tbody>tr>td:nth-child(' + i + ')').data('visible', true);
            }
        };

        Table.prototype.thCount = function thCount() {
            if (this.mode === 'disabled') return 0;
            return this.model.find("thead>tr>*").length;
        };

        Table.prototype.setHideColumns = function setHideColumns() {
            if (this.mode !== 'show') return null;

            for (var _len = arguments.length, hideIndexs = Array(_len), _key = 0; _key < _len; _key++) {
                hideIndexs[_key] = arguments[_key];
            }

            this.hideIndexs = hideIndexs.map(function (i) {
                return String(i);
            });
            for (var i in this.columns) {
                if (this.hideIndexs.indexOf(i) > -1) this.columns[i].hide().data('visible', false);else this.columns[i].show().data('visible', true);
            }
        };

        Table.prototype.getHideColumns = function getHideColumns() {
            return this.hideIndexs;
        };

        Table.prototype.chooseMode = function chooseMode() {
            var _this = this;

            if (this.mode !== 'show') return false;else {
                this.mode = 'choose';

                var _loop = function (i) {
                    _this.columns[i].show();
                    var $th = $(_this.columns[i].get(0));
                    $th.data('_visible', $th.data('visible')).prepend($('<div class="checker chooseHandler"></div>').append($('<span class="' + (_this.columns[i].data('visible') && 'checked') + '"></span>').append($('<input type="checkbox" ' + (_this.columns[i].data('visible') && 'checked') + ' class="group-checkable">').click(function (e) {
                        var checked = e.target.checked;
                        $th.data('_visible', checked);
                        if (checked) {
                            $(e.target).parent().addClass('checked');
                        } else {
                            $(e.target).parent().removeClass('checked');
                        }
                    }))));
                };

                for (var i in this.columns) {
                    _loop(i);
                }
            }
        };

        Table.prototype.cancelChoice = function cancelChoice() {
            if (this.mode !== 'choose') return false;else {
                this.mode = 'show';
                var hideIndexs = [];
                for (var i in this.columns) {
                    var $th = $(this.columns[i].get(0));
                    if ($th.data('visible') === false) hideIndexs.push(String(i));
                    $th.children('.chooseHandler').remove();
                }
                this.setHideColumns.apply(this, hideIndexs);
            }
        };

        Table.prototype.saveChoice = function saveChoice() {
            if (this.mode !== 'choose') return false;else {
                this.mode = 'show';
                var hideIndexs = [];
                for (var i in this.columns) {
                    var $th = $(this.columns[i].get(0));
                    if ($th.data('_visible') === false) hideIndexs.push(String(i));
                    $th.children('.chooseHandler').remove();
                }
                this.setHideColumns.apply(this, hideIndexs);
            }
        };

        Table.prototype.getKey = function getKey() {
            if (this.mode === 'disabled') return null;
            var root = $("body").get(0);
            var getDomKey = function getDomKey(dom) {
                return dom.get(0).tagName + '[' + dom.index() + ']';
            };
            var parent = this.model,
                path = [getDomKey(this.model)];
            while (parent = parent.parent()) {
                path.push(getDomKey(parent));
                if (parent.get(0) === root) break;
            }
            return path.reverse().join('>');
        };

        return Table;
    })();

    var Conf = (function () {
        function Conf(uri) {
            _classCallCheck(this, Conf);

            this.uri = uri;
            this.storage = new Conf.Storage();
            this.confs = this.storage.get('tableUtilConf', []);
        }

        Conf.prototype.search = function search() {
            var _this2 = this;

            var conf = null,
                index = -1;
            this.confs.some(function (obj, i) {
                if (obj.uri === _this2.uri) {
                    conf = obj;
                    index = i;
                    return true;
                }
            });
            return { index: index, conf: conf };
        };

        Conf.prototype.get = function get() {
            var _search = this.search();

            var conf = _search.conf;

            if (conf) {
                return conf.hideIndexs;
            }
            return [];
        };

        Conf.prototype.set = function set(hideIndexs) {
            var _search2 = this.search();

            var obj = _search2.obj;
            var index = _search2.index;

            if (index > -1) {
                this.confs.splice(index, 1);
            }
            this.confs.push({ uri: this.uri, hideIndexs: hideIndexs });
            this.save();
        };

        Conf.prototype.save = function save() {
            if (this.confs.length > 20) {
                this.confs.shift();
                return this.save();
            }
            this.storage.set('tableUtilConf', this.confs, Conf.expires);
        };

        return Conf;
    })();

    Conf.Storage = Cookie;
    Conf.expires = 60 * 60 * 24;

    var Util = (function () {
        function Util(seltor) {
            var _table;

            _classCallCheck(this, Util);

            this.table = new Table(seltor);
            this.conf = new Conf(window.location.pathname + '?' + window.location.search.slice(1).split('&').filter(function (i) {
                    return (/^block=/.test(i) || /^tab=/.test(i)
                    );
                }).join('&') /*+'#'+this.table.getKey()*/);
            (_table = this.table).setHideColumns.apply(_table, this.conf.get());
        }

        Util.prototype.reset = function reset() {
            this.cancel();
            this.table.setHideColumns();
            this.conf.set(this.table.getHideColumns());
        };

        Util.prototype.getMode = function getMode() {
            return this.table.mode;
        };

        Util.prototype.choose = function choose() {
            this.table.chooseMode();
        };

        Util.prototype.save = function save() {
            if (this.table.saveChoice() !== false) {
                this.conf.set(this.table.getHideColumns());
            }
        };

        Util.prototype.cancel = function cancel() {
            this.table.cancelChoice();
        };

        return Util;
    })();

    var instances = [];

    function Factory(seltor) {
        var instance = new Util(seltor);
        instances.push(instance);
        return instance;
    }

    Factory.resetAll = function () {
        instances.map(function (instance) {
            return instance.reset();
        });
        new Conf.Storage().set('tableUtilConf', [], Conf.expires);
    };

    Factory.setStorage = function (adapter) {
        if ('cookie' === adapter) Conf.Storage = Cookie;else if ('localStorage' === adapter) Conf.Storage = LocalStorage;
    };

    Factory.getStorage = function () {
        return new Conf.Storage();
    };

    Factory.setStorageExpires = function (expires) {
        Conf.expires = expires;
    };

    return Factory;
})();

$(function () {
    var model = $(".table").get(0);
    if (model) {
        (function () {

            var generalText = function generalText() {
                $chooseBtn2.children().text('选择');
                $resetBtn2.children().text('重置');
            };

            var saveText = function saveText() {
                $chooseBtn2.children().text('保存');
                $resetBtn2.children().text('取消');
            };

            var table = TableUtil(model),
                $tools2 = $('<li class="dropdown"><a class="btn mini yellow active" href="#" data-toggle="dropdown"><i class="icon-table"></i>表格插件<i class="icon-angle-down"></i></a><ul class="dropdown-menu"></ul></li>'),
                $resetAllBtn2 = $('<li><a class="upload">重置全部</a></li>'),
                $chooseBtn2 = $('<li><a class="upload">选择</a></li>'),
                $resetBtn2 = $('<li><a class="upload">重置</a></li>');
            $tools2.find('.dropdown-menu').append($chooseBtn2).append($resetBtn2).append($resetAllBtn2);

            $(".header .nav.pull-right").prepend($tools2);

            $resetAllBtn2.click(function () {
                TableUtil.resetAll();
                generalText();
            });

            $chooseBtn2.click(function () {
                if ('选择' === $chooseBtn2.children().text()) {
                    table.choose();
                    saveText();
                } else {
                    table.save();
                    generalText();
                }
            });

            $resetBtn2.click(function () {
                if ('重置' === $resetBtn2.children().text()) {
                    table.reset();
                } else {
                    table.cancel();
                }
                generalText();
            });
        })();
    }
});