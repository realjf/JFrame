/**
 * Created by zjj on 17-3-9.
 */

let TableUtil = (() => {
    class Storage {
        set(key, value, expires) {
            throw new Error('abstract')
        }
        get(key, defaultValue = null) {
            throw new Error('abstract')
        }
        del(key) {
            this.set(key, null, -1)
        }
    }

    class Cookie extends Storage {
        set(key, value, expires) {
            let d = new Date()
            d.setTime(d.getTime() + expires * 1000)
            if(expires) {
                document.cookie = `${key}=${JSON.stringify(value)};path=/;expires=${d.toGMTString()}`
            } else {
                document.cookie = `${key}=${JSON.stringify(value)};path=/`
            }
        }
        get(key, defaultValue = null) {
            let a, reg = new RegExp(`(^| )${key}=([^;]*)(;|$)`)
            try {
                return (a = document.cookie.match(reg)) ? JSON.parse(unescape(a[2])) : defaultValue
            } catch (e) {
                return defaultValue
            }
        }
    }

    class LocalStorage extends Storage {
        set(key, value, expires) {
            let expired_at = (new Date).getTime() + expires * 1000
            window.localStorage.setItem(key, JSON.stringify({ value, expired_at }))
        }
        get(key, defaultValue = null) {
            let jsonstr = window.localStorage.getItem(key)
            try {
                let obj = JSON.parse(jsonstr)
                if(obj.expired_at <= (new Date).getTime()) {
                    return obj.value
                }
            } catch (e) {
                console.log(e)
            }
            return defaultValue
        }
    }

    class Table {
        constructor(seltor) {
            this.model = $(seltor)
            this.model.length > 0 && (this.model = $(this.model.get(0)))
            this.mode = 'show'
            this.hideIndexs = []
            if(this.model.length === 0) {
                this.mode = 'disabled'
                return
            }
            this.init()
        }

        init() {
            if(this.mode === 'disabled') return null
            this.columnsLen = this.thCount()
            this.columns = []
            for(let i = 1; i <= this.columnsLen; ++i) {
                this.columns[i] = this.model.find(`thead>tr>*:nth-child(${i}),tbody>tr>td:nth-child(${i})`).data('visible', true)
            }
        }

        thCount() {
            if(this.mode === 'disabled') return 0
            return this.model.find("thead>tr>*").length
        }

        setHideColumns(...hideIndexs) {
            if(this.mode !== 'show') return null
            this.hideIndexs = hideIndexs.map(i => String(i))
            for(let i in this.columns) {
                if(this.hideIndexs.indexOf(i) > -1) this.columns[i].hide().data('visible', false)
                else this.columns[i].show().data('visible', true)
            }
        }

        getHideColumns() {
            return this.hideIndexs
        }

        chooseMode() {
            if(this.mode !== 'show') return false
            else {
                this.mode = 'choose'
                for(let i in this.columns) {
                    this.columns[i].show()
                    let $th = $(this.columns[i].get(0))
                    $th.data('_visible', $th.data('visible')).prepend($('<div class="checker chooseHandler"></div>').append($(`<span class="${this.columns[i].data('visible')&&'checked'}"></span>`).append($(`<input type="checkbox" ${this.columns[i].data('visible')&&'checked'} class="group-checkable">`).click(e => {
                        let checked = e.target.checked
                        $th.data('_visible', checked)
                        if(checked) {
                            $(e.target).parent().addClass('checked')
                        } else {
                            $(e.target).parent().removeClass('checked')
                        }
                    }))))
                }
            }
        }

        cancelChoice() {
            if(this.mode !== 'choose') return false
            else {
                this.mode = 'show'
                let hideIndexs = []
                for(let i in this.columns) {
                    let $th = $(this.columns[i].get(0))
                    if($th.data('visible') === false) hideIndexs.push(String(i))
                    $th.children('.chooseHandler').remove()
                }
                this.setHideColumns(...hideIndexs)
            }
        }

        saveChoice() {
            if(this.mode !== 'choose') return false
            else {
                this.mode = 'show'
                let hideIndexs = []
                for(let i in this.columns) {
                    let $th = $(this.columns[i].get(0))
                    if($th.data('_visible') === false) hideIndexs.push(String(i))
                    $th.children('.chooseHandler').remove()
                }
                this.setHideColumns(...hideIndexs)
            }
        }

        getKey() {
            if(this.mode === 'disabled') return null
            let root = $("body").get(0)
            let getDomKey = dom => `${dom.get(0).tagName}[${dom.index()}]`
            let parent = this.model, path = [getDomKey(this.model)]
            while(parent = parent.parent()) {
                path.push(getDomKey(parent))
                if(parent.get(0) === root) break;
            }
            return path.reverse().join('>')
        }
    }

    class Conf {
        constructor(uri) {
            this.uri = uri
            this.storage = new Conf.Storage
            this.confs = this.storage.get('tableUtilConf', [])
        }

        search() {
            let conf = null, index = -1
            this.confs.some((obj, i) => {
                if(obj.uri === this.uri) {
                    conf = obj
                    index = i
                    return true
                }
            })
            return { index, conf }
        }

        get() {
            let { conf } = this.search()
            if(conf) {
                return conf.hideIndexs
            }
            return []
        }

        set(hideIndexs) {
            let { obj, index } = this.search()
            if(index > -1) {
                this.confs.splice(index, 1)
            }
            this.confs.push({ uri: this.uri, hideIndexs })
            this.save()
        }

        save() {
            if(this.confs.length > 20) {
                this.confs.shift()
                return this.save()
            }
            this.storage.set('tableUtilConf', this.confs, Conf.expires)
        }
    }

    Conf.Storage = Cookie
    Conf.expires = 60*60*24

    class Util {
        constructor(seltor) {
            this.table = new Table(seltor)
            this.conf = new Conf(window.location.pathname+'?'+window.location.search.slice(1).split('&').filter(i => /^block=/.test(i) || /^tab=/.test(i)).join('&')/*+'#'+this.table.getKey()*/)
            this.table.setHideColumns(...this.conf.get())
        }

        reset() {
            this.cancel()
            this.table.setHideColumns()
            this.conf.set(this.table.getHideColumns())
        }

        getMode() {
            return this.table.mode
        }

        choose() {
            this.table.chooseMode()
        }

        save() {
            if(this.table.saveChoice() !== false) {
                this.conf.set(this.table.getHideColumns())
            }
        }

        cancel() {
            this.table.cancelChoice()
        }
    }

    let instances = []

    function Factory(seltor) {
        let instance = new Util(seltor)
        instances.push(instance)
        return instance
    }

    Factory.resetAll = () => {
        instances.map(instance => instance.reset());
        (new Conf.Storage).set('tableUtilConf', [], Conf.expires)
    }

    Factory.setStorage = adapter => {
        if('cookie' === adapter) Conf.Storage = Cookie
        else if('localStorage' === adapter) Conf.Storage = LocalStorage
    }

    Factory.getStorage = () => new Conf.Storage

    Factory.setStorageExpires = expires => {
        Conf.expires = expires
    }

    return Factory
})()



$(() => {
    let model = $(".table").get(0)
    if(model) {
        let table = TableUtil(model),
            $tools2 = $('<li class="dropdown"><a class="btn mini yellow active" href="#" data-toggle="dropdown"><i class="icon-table"></i>表格插件<i class="icon-angle-down"></i></a><ul class="dropdown-menu"></ul></li>'),
            $resetAllBtn2 = $('<li><a class="upload">重置全部</a></li>'),
            $chooseBtn2 = $('<li><a class="upload">选择</a></li>'),
            $resetBtn2 = $('<li><a class="upload">重置</a></li>')
        $tools2.find('.dropdown-menu').append($chooseBtn2).append($resetBtn2).append($resetAllBtn2)


        $(".header .nav.pull-right").prepend($tools2)

        // TableUtil.setStorage('localStorage') // 设置储存(默认cookie), 可选值： cookie or localStorage
        // let table = TableUtil(model),
        //     $table = $(model),
        //     $resetAllBtn = $('<button class="btn blue reset-all-btn">重置全部</button>'),
        //     $chooseBtn = $('<button class="btn green choose-btn">选择</button>'),
        //     $resetBtn = $('<button class="btn red reset-btn">重置</button>'),
        //     $moveHandler = $('<div class="move-handle"><i class="icon-table"></i></div>'),
        //     $tools = $('<div class="table-tools on-collapse"></div>').append($chooseBtn).append($resetBtn).append($resetAllBtn).append($moveHandler)
        // $('body').append($tools)

        // 取上一次记录的位置
        // let storage = TableUtil.getStorage()
        // let { bottom, right } = storage.get('table-util.pos', {
        //     bottom: 100,
        //     right: 100
        // })
        // $tools.css({
        //     right: right + 'px',
        //     bottom: bottom + 'px',
        // })
        //
        // // 点移动
        // let move_mode = false, pos = { x: null, y: null }, collapse = false
        //
        // $moveHandler.on('mousedown', e => {
        //     move_mode = true
        //     $tools.addClass('on-collapse')
        //     pos.x = e.screenX+parseInt($tools.css('right'))
        //     pos.y = e.screenY+parseInt($tools.css('bottom'))
        //     return false
        // }).on('mouseup', e => {
        //     move_mode = false
        //     $tools.removeClass('on-collapse')
        //     return false
        // })
        // $("html").on('mousemove', e => {
        //     if(move_mode) {
        //         $tools.addClass('on-collapse')
        //         $tools.css({
        //             right: `${-e.screenX+pos.x}px`,
        //             bottom: `${-e.screenY+pos.y}px`,
        //         })
        //         storage.set('table-util.pos', {
        //             bottom: parseInt($tools.css('bottom')),
        //             right: parseInt($tools.css('right'))
        //         }, 60*60*24*7)
        //         return false
        //     }
        // })
        //
        // hoverEvent($moveHandler)
        // hoverEvent($resetAllBtn)
        // hoverEvent($chooseBtn)
        // hoverEvent($resetBtn)
        //
        // function hoverEvent(jq) {
        //     jq.hover(() => {
        //         $chooseBtn.blur()
        //         $resetBtn.blur()
        //         $resetAllBtn.blur()
        //         $tools.removeClass('on-collapse')
        //     }, () => {
        //         $chooseBtn.blur()
        //         $resetBtn.blur()
        //         $resetAllBtn.blur()
        //         if($chooseBtn.text() === '选择') {
        //             $tools.addClass('on-collapse')
        //         }
        //     })
        // }

        function generalText() {
            $chooseBtn2.children().text('选择')
            $resetBtn2.children().text('重置')
        }

        function saveText() {
            $chooseBtn2.children().text('保存')
            $resetBtn2.children().text('取消')
        }

        $resetAllBtn2.click(() => {
            TableUtil.resetAll()
            generalText()
        })

        $chooseBtn2.click(() => {
            if('选择' === $chooseBtn2.children().text()) {
                table.choose()
                saveText()

            } else {
                table.save()
                generalText()
            }
        })

        $resetBtn2.click(() => {
            if('重置' === $resetBtn2.children().text()) {
                table.reset()
            } else {
                table.cancel()
            }
            generalText()
        })
    }
})