var Modal = function () {
    var currentModal = '';

    var handleMedal = function() {
        $('a[data-toggle="modal"]').on('click', function(e){
            var modalId = $(this).attr('data-target'), url = $(this).attr('href');
            openModal(url, modalId);
        });
    };

    var openModal = function(url, modalId){
        if (typeof url != 'undefined' || url.indexOf('.html') == -1){
            var medalHtml = '<div id="' + modalId.substr(1) + '" class="modal hide fade" tabindex="0"></div>';
            var $body = $('body');
            $body.append($(medalHtml));
            var $modal = $(modalId);
            $modal.addClass('container');
            $body.modalmanager('loading');
            $modal.load(url, '', function() {
                $modal.modal({backdrop : 'static', keyboard: false}).one('destroy', function() {
                    $modal.removeClass('container');
                });
            });

            $modal.on('hidden.bs.modal', function(){
                setTimeout(function(){$(modalId).remove();}, 1000);
                currentModal = '';
            });
        }

        currentModal = modalId;

    };

    var closeModal = function(modalId){
        modalId = modalId || currentModal;
        $(modalId).modal('hide');
    };

    return {
        //main function to initiate the module
        init: function () {
            handleMedal();
        },
        openModal : function(url, modalId){
            openModal(url, modalId);
        },
        closeModal : function(modalId){
            closeModal(modalId);
        }
    };

}();

Modal.init();