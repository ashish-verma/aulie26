
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/mage',
    'mage/ie-class-fixer'    
], function ($,model) {

//===================================================================

    $(document).ready(function(){
        $(".account-links-menu_btn").click(function(){
            $(".account-links-menu_wrapper").addClass('active');
            $(".account-links-menu").addClass('active');
        });
    });
    $(document).on("click", function(event){
        var $trigger = $(".account-links-menu_btn");
        if($trigger !== event.target && !$trigger.has(event.target).length){
            $(".account-links-menu_wrapper").removeClass('active');
            $(".account-links-menu").removeClass('active');
        }
    });


    /* Footer accordion */

    var flag = true;
    function footer_accordion(){
        var width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        elementTitle = '.footer-col-wrapper .footer-col h4';
        elementHide =  '.footer-col-wrapper .footer-col .footer-col-content';
        elementAdd = 'opened';

        if (width > 767) {
            if (!flag) {
                flag = true;
                $(elementTitle).unbind("click");
                $(elementHide).removeAttr('style');
            }
        } else {
            if (flag) {
                flag = false;
                $(elementTitle).on("click", function(){
                    if ($(this).attr('class') == 'opened') {
                        $(this).removeClass('opened');
                        $(this).next(elementHide).slideToggle();
                    }
                    else {
                        $(this).addClass('opened');
                        $(this).next(elementHide).slideToggle();
                    }
                    return false;
                });
            }
        }
    }

    $(window).on("load resize", function () {
        clearTimeout(this.id);
        this.id = setTimeout(footer_accordion, 500);
    });


    /* Footer accordion END */


//===================================================================
    if($("#product_addtocart_form input[type='checkbox']").length > 0){

        $("#product_addtocart_form input[type='checkbox']").on("click", function(event){          
            
            $("#config_options").modal('openModal');
            if($(this).is(":checked"))
                $(this).parents('.field').nextAll('.field').show();
            else 
                $(this).parents('.field').nextAll('.field').hide();

        });
       
        var checkboxField = $("#product_addtocart_form input[type='checkbox']");
        //$("#product_addtocart_form input[type='checkbox']").trigger("click");

        checkboxField.css({"visibility":'hidden',"position":"absolute"});       
        checkboxField.parents('.field').find(".label").hide();
        checkboxField.next('.admin__field-label').addClass('action primary').show();
        checkboxField.parents('.field').show();
        
        $("#product_addtocart_form input[type='checkbox']").parents('.field').prevAll('.field').show();
        
        
        checkboxField.parents('.field').nextAll('.field').wrapAll('<div id="config_options" class="modal"></div>');
        $('<div id="updatedOptions"></div>').insertAfter(checkboxField.parents('.field')); 
        
         $("#config_options").modal({
            title:'Select Options',
            closed:function () {
                if(!validatePopup()){              
                    checkboxField.prop("checked",false);
                }
                else{
                    $('#updatedOptions').html('').hide();
                    $('#config_options .field.required').each(function(e){
                        clone = $(this).clone();
                        clone.find("select").val($(this).find("select").val());
                        $('#updatedOptions').append(clone);                         
                    });
                }
               
            },
            buttons:[{
                text: 'Save',               
                'class': 'action-primary',
                click: function () { 
                    if(validatePopup()){
                        checkboxField.prop("checked",true);
                        this.closeModal();
                    }else
                        checkboxField.prop("checked",false);                   
                }
            }]
        }); 

        function validatePopup(){
            var isvalid = true;                     
            checkboxField.prop("checked",true);
            $("#config_options .mage-error").remove();
            $('#config_options .field.required input').each(function(e){
                if($(this).val() == ''){
                    $(this).after('<div class="mage-error" generated="true">This is a required field.</div>');
                    isvalid = false;
                }
            });
            $('#config_options .field.required select').each(function(e){                           
                if($(this).val() == null){
                    $(this).after('<div class="mage-error" generated="true">This is a required field.</div>');                                
                    isvalid = false;
                }
            });
            return isvalid;
        }

    }else{
        $('#product-options-wrapper .field').show();
    }

});


