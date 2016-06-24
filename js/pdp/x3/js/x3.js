var x3 = jQuery.noConflict();
x3(function($) {
    pdc = PDC();
    //Customer login via ajax        
    $('.bt-pdp-login-1').hide();
    $('[pdc-data="customer-login"]').click(function() {
        var validate = true;
        if($('#username').val() == ''){
            $('#username').css('border','1px solid red');
            $('#username').focus();
            var validate = false;
        }else{
            var txt = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!txt.test($('#username').val())) {
                $('#username').css('border','1px solid red');
                $('#username').focus();
                var validate = false;
                alert('Please enter a valid email address. For example johndoe@domain.com.');
            } else {
                $('#username').css('border','');
            }
        }
        
        if($('#inputPassword3').val() == ''){
            $('#inputPassword3').css('border','1px solid red');
            if($('#username').val() == ''){
                $('#username').focus();
            }else{
                var txt = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!txt.test($('#username').val())) {
                    $('#username').focus();
                } else {
                    $('#inputPassword3').focus();
                }
            }
            var validate = false;
        }else{
            $('#inputPassword3').css('border','');
        }
        
        if(validate == true){
            var data = $('#pdp-login-form').serialize();
    		data += '&isAjax=1';
            url = $('#pdp-login-form').attr('url');
            $.ajax({
            	type: "POST",
            	url: url,
            	data : data,
            	cache: false,
                beforeSend:  function() {
                    $('.bt-pdp-login-1').show();
                    $('.bt-pdp-login-2').hide();
                },
                success: function(data){   
                    $('.bt-pdp-login-1').hide();
                    $('.bt-pdp-login-2').show();
                    var _json = $.parseJSON(data);
                    if(_json.success == true){
                        $("#is_customer_logged").val(1);
                        pdc.saveDesignToCustomerAccount();
                    }else{
                        $("#is_customer_logged").val('');
                        alert(_json.error);
                    }
                }
            });
        }
        //$('#pdp-login-form').submit();                
        //alert("Edit code here: js/pdp/x3/js/x3.js");
    });
    //Background Color
    $('[pdc-data="CHANGE_BG_COLOR"] li a').click(function() {
        var color = $(this).css('background-color');
        $('[pdc-data="CHANGE_BG_COLOR"] li').removeClass("active");
        $(this).closest("li").addClass("active");
        pdc.changeBackgroundColor(color);
    });
    //Background Colorpicker
    if($("#pdc_background_color_picker").length) {
        $('#pdc_background_color_picker').ColorPicker({
            color: '#006699',
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                $('[pdc-data="color"] div.result').css('backgroundColor', '#' + hex);
                $(".pdc-background-color-list li").removeClass("active");
                pdc.changeBackgroundColor('#' + hex);
            }
        });  
    }
    //Scrollbar
    /**
     * Get inscribed area size
     *
     * @param int oW outer width
     * @param int oH outer height
     * @param int iW inner width
     * @param int iH inner height
     * @param bool R resize if smaller
     */
    function getInscribedArea(oW, oH, iW, iH, R){
        if(!R && iW < oW && iH < oH){
            return {
                "h": iH,
                "w": iW
            };
        }
        if((oW / oH) > (iW / iH)){
            return {
                "h": oH,
                "w": Math.round(oH * iW / iH)
            }
        } else {
            return {
                "h": Math.round(oW * iH / iW),
                "w": oW
            };
        }
    }
    $('.scrollbar-map').scrollbar({
        "onInit": function(){
            this.container.find('.scroll-element_outer').appendTo(this.wrapper);
        },
        "onUpdate": function(container){
            var s = getInscribedArea(150, 150, this.scrollx.size, this.scrolly.size);
            this.scrolly.scroll.height(s.h);
            this.scrollx.scroll.width(s.w);
        },
        "scrollx": $('.scrollbar-map .scroll-element_outer'),
        "scrolly": $('.scrollbar-map .scroll-element_outer'),
        "stepScrolling": false
    });
    //Zoom function
    $("#zoomoptions").change(function() {
        console.info("Set Zoom to canvas");
        pdc.zoomCanvas(null, this.value);
    });
    $(".btn-zoom-in").click(function() {
        pdc.pdcZoom.zoomIn();
    });
    $(".btn-zoom-out").click(function() {
        pdc.pdcZoom.zoomOut();
    });
    $(".btn-zoom-reset").click(function() {
        //Step 1: reset to 100%
        pdc.pdcZoom.resetZoom();
        //Step 2: zoom out to original 100%
        var _canvas = pdc.getCurrentCanvas(),
            _originalScale = _canvas.originalScale || 1;
        pdc.pdcZoom.zoomOutTo(_canvas, _originalScale);
    });
});