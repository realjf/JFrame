(function($){
    var _uploader = {target:null, pk: '', callback: null, domain: ''};

    $.extend({
        bringBack: function(args){
            var callback = _uploader['callback'] || function(target, args){

                var  $parent = _uploader['target'].parent(), url = _uploader['domain'] + args[_uploader['pk']], $input= _uploader['target'].siblings(':input');
                $input.val(args[_uploader['pk']]);
                if ($('.preview-pane', $parent).length == 0){
                    var imgHtml = '<div class="preview-pane"> \
                    <a href="' + url + '" target="_blank"><img src="' + url + '"/> </a>\
                    </div>';
                    $(imgHtml).insertBefore($input);
                } else{
                    $('.preview-pane img', $parent).attr('src', url);
                    $('.preview-pane a', $parent).attr('href', url);
                }
            };
            callback(_uploader['target'], args)
        }
    });

    $.fn.extend({
        imageUploader : function(){
            return this.each(function(){
                var $this = $(this);
                $this.click(function(event){
                    _uploader = $.extend(_uploader, {
                        target: $this,
                        callback : $this.attr('callback'),
                        pk : $this.attr('pk'),
                        domain : $this.attr('domain')
                    });

                    var url = $this.attr("href");
                    Modal.openModal(url, '#image-uploader');
                    return false;
                });
            });
        }
    })

})($);
