var Form = function () {

    var handleForm = function() {
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
                $form = $(form);
                $form.ajaxSubmit({
                    success : function (data){
                        alert(data);
                    }
                });
            }
        });
    };


    return {
        init: function () {
            handleForm();

        }

    };

}();

Form.init();