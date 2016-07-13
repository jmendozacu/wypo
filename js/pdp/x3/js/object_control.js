var x3Controls = jQuery.noConflict();
x3Controls(function($) {
    ///Move html////
    $('#product-image-wrap').append($('#pdc_toolbar_options'));
    ObjEvents = {
        rgb2hex: function(rgb) {
            var check = rgb.split('(');
            if(check[0]=='rgb'){
                var hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
                rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
                function hex(x) {
                    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
                }
                return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
            }else{
                return rgb;
            }
        },
        init_text: function (obj){
            if(obj){
                $('.pdc_edit_text_only').show();
                $('[pdc-data="text"]').val(obj.text);
                var font_size = obj.get('fontSize');
                $('.pdc-style').val(font_size);
                $('.pdc-list-tool').removeClass('on');
                if(obj.get('fontWeight')=='bold'){
                    $('.text-bold').addClass('on');    
                }
                if(obj.get('fontStyle')=='italic'){
                    $('.text-italic').addClass('on');    
                }
                var text_deco = obj.get('textDecoration');
                if (text_deco.indexOf('underline') > 0) { 
                    $('.text-underline').addClass("on");
                }
                if (text_deco.indexOf('overline') > 0) {
                    $('[pdc-text="overline"]').addClass("active");
                }
                if (text_deco.indexOf('line-through') > 0) {
                    $('[pdc-text="line-through"]').addClass("active");
                }
                var text_align = obj.get('textAlign');
                $('.text-align.on').removeClass('on');
                if(text_align=='right'){
                    $('.text-right').addClass("on");
                }else{
                    if(text_align=='center'){
                        $('.text-center').addClass("on");
                    }else{
                        $('.text-left').addClass("on");
                    }
                }
                var text_font = obj.get('fontFamily');
                //Prop selected font
                $('.pdc-style-family option').removeAttr("selected");
                $('.pdc-style-family [style="font-family:'+ text_font +';"]').prop("selected", true);
                $('.pdc-style-family').css("font-family", text_font);
                ObjEvents.init_color_item(obj);
            }
        },
        hide_popup_block: function(act){
            $('.pdc-item-tool ul').each(function(){
                if(!$(this).hasClass('current')){
                    $(this).hide();
                }
            });
            $('.pdc-transparency-slider').hide();
        },
        init_opacity: function(obj){
            var opacity = obj.get('opacity');
            $('.pdc-transparency-slider input').val(opacity*100);
        },
        objectSelected: function(){
            var canvas = pdc.getCurrentCanvas();
            var act = canvas.getActiveObject();
            ObjEvents.showTextEffectControls(act);
            if(!act) return false;
            if((act.type=='text')||(act.type=='i-text') || (act.type=='curvedText')){
                $('#pdc_toolbar_options').removeClass('objimg');
                $('#pdc_text_edit').show();
                ObjEvents.init_text(act);
            }else{
                $('#pdc_toolbar_options').addClass('objimg');
                $('#pdc_text_edit').hide();
                $('.pdc_edit_text_only').hide();
            }
            ObjEvents.updateObjPos();
            ObjEvents.init_opacity(act);
            ObjEvents.init_color_item(act);
            var name = act.name;
            $('[pdc-block="layer"] .active').removeClass('active');
            $('[pdc-block="layer"] [name="'+name+'"]').addClass('active');
            $(".pdc-item-tool ul, .pdc-transparency-slider").hide();
        },
        showTextEffectControls: function(act) {
            $('[pdc-box="curved"], [pdc-box="small-large-font"]').hide();
            if(!act) return false;
            if(act.type && act.type == "curvedText") {
                //Object types: curved, arc, smallToLarge, largeToSmallTop, largeToSmallBottom, bulge, STRAIGHT
                if(act.effect && (act.effect == 'arc' || act.effect == 'curved')) {
                    //Update angle, space for controls
                    if(act.radius) {
                        $('[pdc-box="curved"] [name="radius"]').val(act.radius);
                        $('[pdc-box="curved"] [name="radius_number"]').val(act.radius);
                    }
                    if(act.spacing) {
                        $('[pdc-box="curved"] [name="spacing"]').val(act.spacing);    
                        $('[pdc-box="curved"] [name="spacing_number"]').val(act.spacing);    
                    }
                    if(act.reverse) {
                        $('[pdc-box="curved"] [name="reverse"]').prop("checked", true);
                    } else {
                        $('[pdc-box="curved"] [name="reverse"]').prop("checked", false);
                    }
                    $('[pdc-box="curved"]').show();
                } else {
                    if(act.smallFont) {
                        $('[pdc-box="small-large-font"] [name="text_small_font"]').val(act.smallFont);
                        $('[pdc-box="small-large-font"] [name="text_small_font_number"]').val(act.smallFont);
                    }
                    if(act.largeFont) {
                        $('[pdc-box="small-large-font"] [name="text_large_font"]').val(act.largeFont);
                        $('[pdc-box="small-large-font"] [name="text_large_font_number"]').val(act.largeFont);
                    }
                    $('[pdc-box="small-large-font"]').show();
                }
            } 
        },
        updateObjPos: function(){
            var canvas = pdc.getCurrentCanvas(),
                act = canvas.getActiveObject(),
                pos_top_2 = left_pos = 0,
                zoom = canvas.scale,
                max_cv_width = $('#product-image-wrap').width(),
                max_cv_height = $('#product-image-wrap').height(),
                pos_top = parseFloat($('#pdc_toolbar_options').height());
                width_pos = parseFloat($('#pdc_toolbar_options').width());
            if(!act) return false;
            var pos_top_act = act.getBoundingRect();
            //console.log(width_pos);
            left_pos = pos_top_act.left + (pos_top_act.width - width_pos)/2;
            if(pos_top_act.top > (pos_top + 50)){
                pos_top_2 = parseFloat(pos_top_act.top) - (pos_top + 50);
            }else{
                pos_top_2 = parseFloat(pos_top_act.top) + pos_top + 20 + pos_top_act.height;
            }
            if(left_pos < 0) { left_pos = 0;}
            if(left_pos > (max_cv_width - width_pos)) { left_pos = max_cv_width - width_pos;}
            if(pos_top_2 < 0) { pos_top_2 = 0;}
            if(pos_top_2 > (max_cv_height - pos_top)) { pos_top_2 = max_cv_height - pos_top;}
            $('#pdc_toolbar_options').css({
                left: left_pos,
                top: pos_top_2,
            }).show();
            //allow scoll after object moved
            pdc.getCurrentCanvas().allowTouchScrolling = true;
        },
        objectUnselected: function(){
            $('#pdc_toolbar_options').hide();
        },
        objMoving: function(){
            $('#pdc_toolbar_options').hide();
            //Not scoll when object is moving
            pdc.getCurrentCanvas().allowTouchScrolling = false;
        },
        editText: function(act,val){
              var canvas = pdc.getCurrentCanvas();
              var active = canvas.getActiveObject();
              if (!active){
                if(act=='text'){
                   pdc.addText(val); 
                }
                return;
              }else{
                if (act == 'textAlign'){
                    var left    = active.left,
                        top     = active.top,
                        width   = active.width,
                        height  = active.height;
                    //active.set({ originX : val, left : left ,top : top });
                    active.set(act,val);
                }
                if((active.type!='i-text')&&(active.type!='text')&&(active.type!='curvedText')){
                    if(act=='text'){
                       canvas.deactivateAll().renderAll(); 
                       pdc.addText(val); 
                    }
                }else{
                    if(act=='text'){
                        var scaleXobj =  active.scaleX,
                            scaleYobj = active.scaleY;
                            active.set({
                                scaleX: 1,
                                scaleY: 1
                            })
                            active.setText(val);
                		    canvas.renderAll();    
                            active.set({
                                scaleX: scaleXobj,
                                scaleY: scaleYobj
                         });
                		canvas.renderAll();
                    }else{
                        ObjEvents.setStyle(active, act, val);
                    }
                }
                canvas.renderAll();
                ObjEvents.init_color_item(active);
              }
              PDC_layer.load_layer();
        },
        setStyle: function(object, styleName, value) {
          var canvas = pdc.getCurrentCanvas();
          if (object.setSelectionStyles && object.isEditing) {
            var style = { };
            style[styleName] = value;
            object.setSelectionStyles(style).setCoords();
          }
          else {
            object[styleName] = value;
          }
          canvas.renderAll();
        },
        getStyle: function(object, styleName) {
          return (object.getSelectionStyles && object.isEditing)
            ? object.getSelectionStyles()[styleName]
            : object[styleName];
        },
        addHandler: function(id, fn, eventName) {
          document.getElementById(id)[eventName || 'onclick'] = function() {
            var el = this;
            if (obj = canvas.getActiveObject()) {
                fn.call(el, obj);
                canvas.renderAll();
            }
          };
        },
        init_color_item: function(obj){
            var canvas = pdc.getCurrentCanvas();
            var color = obj.get('fill'); 
            $("#pdc_toolbar_options .pdc-colors").hide();
            //Check object can change color or not
            if(!this.isObjectCanChangeColor(obj)) {
                return false;
            }         
            $("#pdc_toolbar_options .pdc-colors").show();
            $('[pdc-data="color"]').ColorPicker({
            	color: color,
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
                    ObjEvents.editItem('color','#' +hex);
                    //$('.input_picker').val('#' +hex);
            	}
            });        
            $('input[name="color_filling"]').val(color);
        },
        isObjectCanChangeColor: function(obj) {
            var canvas = pdc.getCurrentCanvas();
            if(obj.type == "image") return false;
            if(obj.type == "path-group") {
                $("#pdc_toolbar_options").addClass("fill");
                if(!(obj.isSameColor && obj.isSameColor() || !obj.paths)) {
                    $("#pdc_toolbar_options").removeClass("fill");
                    return false;
                }
            }
            return true;
        },
        copyObject: function () {
            var canvas = pdc.getCurrentCanvas();
            var active = canvas.getActiveObject();
            if (!active) return;
            if (fabric.util.getKlass(active.type).async) {
                active.clone(function (clone) {
                    clone.set({
                        isrc: active.isrc,
                        price: active.price,
                        left: active.left + 20,
                        top: active.top + 20,
                        borderColor: '#808080',
                        cornerColor: 'rgba(68,180,170,0.7)',
                        cornerSize: 16,
                        cornerRadius: 12,
                        transparentCorners: false,
                        centeredScaling:true,
                        rotatingPointOffset: 40,
                        padding: 5
                    });
                    clone.setControlVisible('mt', false);
                    canvas.add(clone);
                    $(".pdc-more-tool .pdc-list-tool").hide();
                });
            } else {
                var clone = active.clone().set({
                    borderColor: '#808080',
                    cornerColor: 'rgba(68,180,170,0.7)',
                    cornerSize: 16,
                    cornerRadius: 12,
                    transparentCorners: false,
                    centeredScaling:true,
                    rotatingPointOffset: 40,
                    padding: 5,
                    left: active.left + 30,
                    top: active.top + 30,
                    price: active.price || 0
                });
                clone.setControlVisible('mt', false);
                canvas.add(clone);
                $(".pdc-more-tool .pdc-list-tool").hide();
            }
            //canvasEvents.addlayer();
            canvas.renderAll();
        },
        editItem: function(task,value){
            var canvas = pdc.getCurrentCanvas();
            var active = canvas.getActiveObject();
            if (!active) return;
            switch (task) {
                case 'sendBackwards' : ObjEvents.sendBackwards(active); break;
                case 'sendToBack' : canvas.sendToBack(active); break;
                case 'bringForward' : canvas.bringForward(active); break;
                case 'bringToFront' : canvas.bringToFront(active); break;
                case 'flipX'    : active.flipX = active.flipX ? false : true; break;
                case 'flipY'    : active.flipY = active.flipY ? false : true; break;
                case 'delete'   : canvas.remove(active); break;
                case 'duplicate': this.copyObject(); break;
                case 'fontFamily': active.set('fontFamily',value); console.log(value); break;
                case 'color'    : 
                    if(active.type=='i-text'){ 
                        ObjEvents.setStyle(active, 'fill', ObjEvents.rgb2hex(value)); }
                            else{ if(active.type=='curvedText'){
                        var scaleXobj =  active.scaleX,
                            scaleYobj = active.scaleY;
                        active.set({
                            scaleX: 1,
                            scaleY: 1
                        })
            			active.set('fill',ObjEvents.rgb2hex(value)); 
            		      canvas.renderAll();    
                        active.set({
                            scaleX: scaleXobj,
                            scaleY: scaleYobj
                        })
            			canvas.renderAll();
                    }else{ active.set('fill',ObjEvents.rgb2hex(value)); } 
                    }   break;
                case 'opacity'  :   active.set('opacity',value);    break; 
                case 'fontSize'  :
                    var scaleXobj =  active.scaleX,
                        scaleYobj = active.scaleY;
                        active.set({
                            scaleX: 1,
                            scaleY: 1
                        })
            			active.set({
                            fontSize: value,
                            smallFont: parseInt(value /2), //For some special effect
                            largeFont: value
                        }); 
            		    canvas.renderAll();    
                        active.set({
                            scaleX: scaleXobj,
                            scaleY: scaleYobj
                        });
            			canvas.renderAll();
                    break; 
                case 'move': 
                    var zoom = canvas.getZoom(),
                        w_canvas =   canvas.width/zoom,
                        h_canvas =   canvas.height/zoom,
                        w_obj =   active.getWidth(),
                        h_obj =   active.getHeight();
                    switch (value) {
                        case 'm_tl': active.set({"left": 1,"top": 1}); break;  //ok
                        case 'm_tc': active.set({"left": w_canvas/2 - w_obj/2 - 1,"top": 1}); break;
                        case 'm_tr': active.set({"left": parseInt(w_canvas) - parseInt(w_obj) + 1,"top": 1}); break;
                        case 'm_cl': active.set({"left": 1,"top":h_canvas/2-h_obj/2});  break; //ok
                        case 'm_cc': active.set({"top": h_canvas/2-h_obj/2-1,"left":w_canvas/2 - w_obj/2 - 1}); break; //ok
                        case 'm_cr': active.set({"left": w_canvas - w_obj - 1,"top":h_canvas/2-h_obj/2}); break;
                        case 'm_bl': active.set({"left": 1,"top": h_canvas - h_obj - 1}); break;
                        case 'm_bc': active.set({"top": h_canvas - h_obj - 1,"left":w_canvas/2 - w_obj/2 - 1}); break;
                        case 'm_br': active.set({"left": w_canvas - w_obj + 1,"top": h_canvas - h_obj - 1}); break;
                    }
                    //canvas.centerObjectH(active);
                    //canvas.centerObjectV(active);
                    break;
            }      
            active.setCoords();
            canvas.renderAll();
        },
        sendBackwards: function(activeObj) {
            var _canvas = pdc.getCurrentCanvas();
            var objIndex = _canvas.getObjects().indexOf(activeObj),
                minIndex = 0;
            _canvas.forEachObject(function(obj) {
                if(obj.object_type && (obj.object_type == "background" || obj.object_type == "background_color")) {
                    minIndex += 1;
                }
            });
            //If canvas have pattern, then don't allow sending object to behind the pattern
            if(objIndex == minIndex) {
                return false;
            }
            activeObj.sendBackwards();
            _canvas.renderAll();
        }
    }
    $.each(pdc.allCanvas, function(i, canvas) {
		canvas.observe('object:moving', ObjEvents.objMoving);
		canvas.observe('object:selected', ObjEvents.objectSelected);
		canvas.observe('before:selection:cleared', ObjEvents.objectUnselected);
		canvas.observe('mouse:up', ObjEvents.updateObjPos);
		canvas.observe('object:rotating', ObjEvents.objMoving);
		canvas.observe('object:scaling', ObjEvents.objMoving);
	});
    ////Action to edit////////
    $('.pdc-element-transparency').click(function(){
        $('.pdc-list-tool').hide();
        $('.pdc-transparency-slider').show();
    })
    $('.pdc-transparency-slider input').change(function(){
        var opacity = $(this).val()/100;
        ObjEvents.editItem('opacity',opacity);      
    })
    $('.pdc-more-tool > a').click(function(){
        $(this).next().addClass('current').toggle();
        ObjEvents.hide_popup_block();
    })
    $('.pdc-colors > a').click(function(){
        $('.pdc-color-list').toggle();
    })
    $('.pdc-color-list a').click(function(){
        var color = $(this).css('background-color');
        $('.pdc-color-list li').removeClass("active");
        $(this).closest("li").addClass("active");
        ObjEvents.editItem('color',color);
    });
    $('.pdc-style-family').on('change', function () { var fontFamily = $(this).find('option:selected').css('font-family'); $(this).css('font-family',fontFamily); ObjEvents.editItem('fontFamily',fontFamily); });
    $('.pdc-style').on('change', function () { ObjEvents.editItem('fontSize',$(this).val()) });
    $('.pdc-element-copy').on('click', function () { ObjEvents.editItem('duplicate'); });
    $('.pdc-del-tool a').on('click', function () { ObjEvents.editItem('delete'); });
    $('.pdc-element-forward').on('click', function () { ObjEvents.editItem('bringForward'); });
    $('.pdc-element-backward').on('click', function () { ObjEvents.editItem('sendBackwards'); });
    $('.pdc-list-tool .text-bold').on('click', function () { 
        $(this).toggleClass('on');
        if($(this).hasClass('on')){
            font_weight = 'bold';
        }else{
            font_weight = 'Normal';
        }
        ObjEvents.editText('fontWeight',font_weight); 
    });
    $('.pdc-list-tool .text-underline').on('click', function () { 
        $(this).toggleClass('on');
        if($(this).hasClass('on')){
            font_weight = 'underline';
        }else{
            font_weight = '';
        }
        ObjEvents.editText('textDecoration',font_weight);
    });
    $('.pdc-list-tool .text-italic').on('click', function () { 
        $(this).toggleClass('on');
        if($(this).hasClass('on')){
            font_weight = 'italic';
        }else{
            font_weight = '';
        }
        ObjEvents.editText('fontStyle',font_weight); 
    });
    $('.text-align').click(function(){
        if(!$(this).hasClass('on')){
           var text_algin = 'left';
           $('.text-align').removeClass('on');
           $(this).addClass('on');
           if($(this).hasClass('text-left')){
              text_algin = 'left';
           } 
           if($(this).hasClass('text-center')){
                text_algin = 'center';
           }
           if($(this).hasClass('text-right')){
                text_algin = 'right';
           }
           ObjEvents.editText('textAlign',text_algin);
        }
    });
    $('[pdc-data="text"]').on('keyup',function(){
             ObjEvents.editText('text',$(this).val());
    });
    var _defaultText = ["add heading", "add subheading", "add a little bit of body text"];
    //Make sure the text after translate
    $("#text .textControls li").each(function() {
        if($(this).text()) {
            var _text = $(this).text().toLowerCase().trim();
            if($.inArray(_text, _defaultText) == -1) {
                _defaultText.push(_text);   
            }
        }
    });
    $('[pdc-data="text"]').on('focus',function(){
        var text = $(this).val().toLowerCase().trim();
        if($.inArray(text, _defaultText) !== -1) {
            $(this).val("");
        }
    });
    $('.pdc-flipx').on('click', function () { ObjEvents.editItem('flipX'); });
    $('.pdc-flipy').on('click', function () { ObjEvents.editItem('flipY'); });
    //$('#move_to_back').on('click', function () { ObjEvents.editItem('sendToBack'); });
    //$('#move_to_front').on('click', function () { ObjEvents.editItem('bringToFront'); });
});