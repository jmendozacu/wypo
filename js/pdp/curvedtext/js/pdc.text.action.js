var pdc_text_curved = jQuery.noConflict();
pdc_text_curved(document).ready(function($){
    $('[pdc-effect]').click(function(){
        var canvas = pdc.getCurrentCanvas();
        var active = canvas.getActiveObject();
        if (!active) {
            alert("Please select a text!");
            return false;
        }
        var effect = $(this).attr('pdc-effect'),
            fontSize = parseInt(active.fontSize),
            largeFont = fontSize,
            smallFont = fontSize;
        if(!active.largeFont) {
            smallFont = parseInt(largeFont / 2);
        } else {
            largeFont = parseInt(active.largeFont);
            smallFont = parseInt(active.smallFont);
                //Someone might change the font from transform slider, just roll back to make it look like sample text
            if(smallFont > largeFont) {
                var _tempLarge = largeFont;
                largeFont = smallFont,
                smallFont = _tempLarge;
            }
        }
        if(effect == "obulge") {
            if(smallFont < largeFont) {
                smallFont = largeFont;
                largeFont =  parseInt(smallFont / 2);
            }
            effect = "bulge";
        }
        if((active.type=='text')||(active.type=='i-text')){
            var CurvedText = new fabric.CurvedText(active.text,{
                left: active.left,
                top: active.top,
                textAlign: 'center',
                fill: active.fill,
                radius: 100,
                fontSize: fontSize,
                spacing: 15,
                fontFamily: active.fontFamily,
                name: active.name,
                scaleX: active.scaleX,
                scaleY: active.scaleY,
                opacity: active.opacity,
                fontWeight: active.fontWeight,
                fontStyle: active.fontStyle,
                price: active.price,
                effect: effect,//curved, arc, smallToLarge, largeToSmallTop, largeToSmallBottom, bulge, STRAIGHT
                angle: active.angle,
                smallFont: smallFont,
                largeFont: largeFont,
                borderColor: '#808080',
                cornerColor: 'rgba(68,180,170,0.7)',
                cornerSize: 16,
                cornerRadius: 12,
                transparentCorners: false,
                centeredScaling:true,
                rotatingPointOffset: 40,
                padding: 5
            });
          canvas.remove(active);
          canvas.add(CurvedText).setActiveObject(CurvedText).calcOffset().renderAll();
        }else if(active.type=='curvedText'){
            active.set({
                effect: effect,
                smallFont: smallFont,
                largeFont: largeFont
            });
            canvas.renderAll();
            ObjEvents.showTextEffectControls(active);
        }else{
            ////do nothing/////
            $('[ pdc-box="curved"]').hide();
        }
    })
    $('#pdc_ctext_reverse').click(function(){
        var canvas = pdc.getCurrentCanvas();
		var obj = canvas.getActiveObject(); 
		if(obj){
		    var scaleXobj =  obj.scaleX,
                scaleYobj = obj.scaleY;
           /* obj.set({
                scaleX: 1,
                scaleY: 1
            })*/
			obj.set('reverse',$(this).is(':checked')); 
			canvas.renderAll();
            obj.set({
                scaleX: scaleXobj,
                scaleY: scaleYobj
            })
			canvas.renderAll();
		}
	});
	$('#pdc_ctext_radius, #pdc_ctext_spacing').change(function(e){
        //update pdc_ctext_radius number
        if(e.target.id) {
            if($("#" + e.target.id + "_number").length) {
                $("#" + e.target.id + "_number").val(this.value);
            }
        }
        var canvas = pdc.getCurrentCanvas();
		var obj = canvas.getActiveObject(); 
		if(obj){
		      var scaleXobj =  obj.scaleX,
                scaleYobj = obj.scaleY;
            obj.set({
                scaleX: 1,
                scaleY: 1
            });
			obj.set($(this).attr('name'),$(this).val()); 
		   canvas.renderAll();    
            obj.set({
                scaleX: scaleXobj,
                scaleY: scaleYobj
            })
			canvas.renderAll();     
		}
	});
    $("#pdc_ctext_radius_number, #pdc_ctext_spacing_number, #text_small_font_number, #text_large_font_number").change(function(e) {
        if(!e.target.id) {
            console.warn("No selector found!");
            return;
        }
        var sliderInput = $("#" + e.target.id.replace("_number", ""));
        if(sliderInput.length) {
            if(sliderInput.attr("min") <= this.value <= sliderInput.attr("max")) {
                sliderInput.val(this.value);
                sliderInput.change();   
            } else {
                $(this).val(sliderInput.val());
            }   
        }
    });
    //Text effect font slider
    $('#text_small_font, #text_large_font').change(function(e){
        //update pdc_ctext_radius number
        if(e.target.id) {
            if($("#" + e.target.id + "_number").length) {
                $("#" + e.target.id + "_number").val(this.value);
            }
        }
        var canvas = pdc.getCurrentCanvas();
		var obj = canvas.getActiveObject();
		if(obj && obj.effect){
            var effects = ['bulge', 'smallToLarge', 'largeToSmallTop', 'largeToSmallBottom'];
            if($.inArray(obj.effect, effects) !== -1) {
                var smallFont = $("#text_small_font").val(),
                    largeFont = $("#text_large_font").val();
                obj.set({
                    smallFont: smallFont,
                    largeFont: largeFont
                });
                canvas.renderAll();   
            }     
		}
	});
})