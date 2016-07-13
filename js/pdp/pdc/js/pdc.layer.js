var pdc_layer = jQuery.noConflict();
pdc_layer(document).ready(function($){
    //pdc = PDC();
    var currency_symbol = $("#currency_symbol").val();
    PDC_layer = {
        init: function(){
            var canvas = pdc.getCurrentCanvas();
            ////////////First Load /////////////////////
            if(!$('[pdc-block="layer"]').hasClass('loaded')){
                //$('[pdc-block="layer"]').draggable({ handle: 'label[pdc-label-drag]' });
                $('[pdc-block="layer"]').on('click','[pdc-layer-info]',function(){
                    var layer_item = $(this).parent(),
                        layer_item_index = layer_item.attr('pdc-layer');
                    if(layer_item_index!=0){
                        if($(this).attr('pdc-layer-info')=='del'){
                            layer_item.remove();
                            PDC_layer.removeItemFromLayer(layer_item.attr('name'));
                            if($(this).hasClass('mask')){
                                canvas.setOverlayImage(null, canvas.renderAll.bind(canvas));
                            }
                            canvas.deactivateAll()();
                        }else{
                            $('[pdc-block="layer"] .active').removeClass('active');
                            if(!$(this).parent().hasClass('pdc_layer_uncheck')){
                                layer_item.addClass('active');
                            }
                        }
                        if($(this).attr('pdc-layer-info')=='lock'){
                            ///////////////Lock item//////////////////////
                            var act = $(this).hasClass('lock') ? true : false;
                            PDC_layer.lock_obj(layer_item.attr('name'),act);
                            $(this).toggleClass('lock');
                        }
                        if($(this).parent().find('[pdc-layer-info="lock"]').hasClass('lock')){
                            $('#pdc_toolbar_options').hide();
                        }else{
                            if(!$(this).parent().hasClass('pdc_layer_uncheck')){
                                $('#pdc_toolbar_options').show();
                            }
                        }
                        if($(this).parent().find('[pdc-layer-info="lock"]').hasClass('lock')){
                            
                        }else{
                            if(!$(this).parent().hasClass('pdc_layer_uncheck')){
                                PDC_layer.activeobj(layer_item.attr('name'));
                            }
                        }
                    }
                })
                $('[pdc-block="layer"]').addClass('loaded');
            }
            PDC_layer.load_layer();
            $.each(pdc.allCanvas, function(i, canvas) {
                canvas.observe('object:added', PDC_layer.load_layer);
                canvas.observe('object:removed', PDC_layer.load_layer);
            });
            //canvas.observe('object:selected', PDC_layer.objSelect);
        },
        load_layer_first_time: function(){
            var canvas = pdc.getCurrentCanvas();
            var objects = canvas.getObjects();
            if(objects.length > 0) {
                objects.forEach(function(o) {
                    var html = $('[pdc-block="layer"] pdc-layer="0"');
                    $('[pdc-block="layer"] [pdc-layer="content"]').append(html);
                    var html = '<li>';
                    if((o.type=='text')||(o.type=='i-text') || (o.type=='curvedText')){
                        html  +=   o.text;
                    }
                    if((o.type=='path-group')||(o.type=='image')){
                        html  +=   '<img src="'+o.isrc+'"/>';
                    }
                    html+='<i class="pdc_layer_delete">Del</i>';
                    html += '</li>';
                });
            }
        },
        objSelect: function () {
            var canvas = pdc.getCurrentCanvas();
            var active = canvas.getActiveObject();
            if(!active)return;
            var name = active.name;
            PDC_layer.activeobj(name);
            $('[pdc-block="layer"] .active').removeClass('active');
            $('[pdc-block="layer"] [name="'+name+'"]').addClass('active');
                        
        },
        lock_obj: function(name,act){
            var canvas = pdc.getCurrentCanvas();
            var objects = canvas.getObjects();
            if((name!='')&&(name!=undefined)){
                for (var i = 0; i < objects.length; i++) {
                    if(objects[i].name==name){
                        objects[i].set({
                            selectable: act
                        });
                        if(!act){
                            canvas.deactivateAll().renderAll();
                            $('[pdc-box="toolbox"]').hide();
                        }
                        canvas.renderAll();
                    }
                }
            }
        },
        load_final_price: function(){
            //$('#final_price').val();
        },
        activeobj: function(el){
            var canvas = pdc.getCurrentCanvas();
            var objects = canvas.getObjects();
            if((el!='')&&(el!=undefined)){
                for (var i = 0; i < objects.length; i++) {
                    if(objects[i].name==el){
                        canvas.setActiveObject(objects[i]);
                    }
                }
            }
        },
        load_layer: function(){ 
            var canvas = pdc.getCurrentCanvas();
            var html = $('[pdc-block="layer"] [pdc-layer="0"]').html();
            $('[pdc-block="layer"] tbody').html('<tr style="display:none;" pdc-layer="0">'+html+'</tr>');
            var objects = canvas.getObjects(),
                hasDesignItem = false;
            $(".layer-final").hide();
            $(".design-cost").hide();
            if(objects.length) {
                //Make sure design has design item, skip background color, background_image
                objects.forEach(function(o) {
                    if(o.object_type && (o.object_type == "background_color" || o.object_type == "background")) {
                        if(o.object_type == "background") {
                            var o_src = o.isrc || o.src;
                            if(o_src && o_src.match("images/artworks/")) {
                                hasDesignItem = true;
                            }
                        }
                    } else {
                        hasDesignItem = true;
                    }
                });
            }
            //console.info(objects);
            if(hasDesignItem) {
                //PDC_layer.updatePosition();
                var i = 0,price=0;
                objects.forEach(function(o) {
                    if(o.object_type!='background_color'){ 
                        var o_src = o.isrc || o.src;
                        if(o.object_type=='background'){ 
                            //Skip original background, show background or pattern from images only
                            if(o_src && !o_src.match("images/artworks/")) {
                                return;
                            }
                        }
                        i++; 
                        var name = 'item_'+i;
                        o.set({name:name}); canvas.renderAll();
                        if((o.price=='')||(o.price==undefined)){o.price=0;}
                        price+=parseFloat(o.price);
                        $('[pdc-block="layer"] tbody').append('<tr obj_type="'+o.object_type+'" pdc-layer="'+i+'" name="'+name+'">'+html+'</tr>');
                        if((o.type=='text')||(o.type=='i-text')||(o.type=='curvedText')){
                            $('[pdc-layer="'+i+'"] [pdc-layer-info="type"]').html(o.text.substring(0,10));
                        } 
                        if((o.type=='image')||(o.type=='path-group')){                             
                            $('[pdc-layer="'+i+'"] [pdc-layer-info="type"]').html('<img src="'+o_src+'" />');
                        }
                        if(o.object_type=='background'){
                            $('[pdc-layer="'+i+'"] [pdc-layer-info="lock"] i').hide();
                            //$('[pdc-layer="'+i+'"] [pdc-layer-info="type"]').html('<span class="pdc_layer_bg" style="background-color:'+o.fill+'"></span>');
                        }
                        $('[pdc-layer="'+i+'"] [pdc-layer-info="price"]').html(currency_symbol+parseFloat(o.price).toFixed(2));
                        $('[pdc-layer="'+i+'"] [pdc-layer-info="size"]').html(parseInt(o.width*o.scaleX) + 'X' + parseInt(o.height*o.scaleY));
                        //$('[pdc-layer-info="size"]').html(o.price);
                    }else{
                        $('#pdc_block_layer').attr('bg_color',o.fill);
                    }
                });
                //Add overlay Image to layer
                if(canvas.overlayImage){
                    i++; 
                    var o = canvas.overlayImage,name = 'item_'+i;
                    var o_src = o.isrc || o.src;
                    //Show layer from artwork type = mask only. Not show original overlay layer
                    if(o_src && o_src.match("images/artworks/")) {
                        o.set({name:name}); canvas.renderAll();
                        if((o.price=='')||(o.price==undefined)){o.price=0;}
                        price+=parseFloat(o.price);
                        $('[pdc-block="layer"] tbody').append('<tr class="pdc_layer_uncheck" pdc-layer="'+i+'" name="'+name+'">'+html+'</tr>');
                        $('[pdc-layer="'+i+'"] [pdc-layer-info="price"]').html(currency_symbol+parseFloat(o.price).toFixed(2));
                        $('[pdc-layer="'+i+'"] [pdc-layer-info="size"]').html(parseInt(o.width*o.scaleX) + 'X' + parseInt(o.height*o.scaleY));
                        if(o.object_type=='mask'){                             
                            $('[pdc-layer="'+i+'"] [pdc-layer-info="type"]').html('<img src="'+o_src+'" />');
                            $('[pdc-layer="'+i+'"] .action i').hide();
                            $('[pdc-layer="'+i+'"] .del').addClass('mask');
                        }
                        if($("#d-pcolors").length > 0){
                            $('[pdc-layer="'+i+'"] .del, [pdc-layer="'+i+'"] .action').hide();
                        }   
                    }
                }
                //console.info("Show side cost", pdc.getSidesConfig());
                //Show side's cost in layer
                var _sideConfig = pdc.getSidesConfig(),
                    _currentSideId = pdc.getActiveSide().id;
                if(_sideConfig && _currentSideId && _sideConfig[_currentSideId]) {
                    var _sideCost = _sideConfig[_currentSideId].price;
                    if(parseFloat(_sideCost) > 0) {
                        price += parseFloat(_sideCost);
                        var _priceFormated = currency_symbol + parseFloat(_sideCost).toFixed(2);
                        $(".design-cost .price_layer").html(_priceFormated);
                        $(".design-cost").show();       
                    }
                }
                $('[obj_type="background"] img').css('background-color',$('#pdc_block_layer').attr('bg_color'));
                $('[pdc-block="layer-final"] .price_layer').html(currency_symbol+parseFloat(price).toFixed(2));
                $(".layer-final").show();
            }
            PDC_layer.modify_layer();
        },
        modify_layer: function(){
            var canvas = pdc.getCurrentCanvas();
            var active = canvas.getActiveObject();
            if (!active) return;
            var name = active.name;
            $('[pdc-block="layer"] .active').removeClass('active');
            $('[pdc-block="layer"] [name="'+name+'"]').addClass('active');
        },
        update_price: function(){
            var canvas = pdc.getCurrentCanvas();
            var objects = canvas.getObjects(),price = 0;
            if(objects.length > 0) {
                objects.forEach(function(o) { 
                    if (o.price == undefined) {o.price = 0;}
                    price+=parseFloat(o.price);
                })
            }
            $('[pdc-block="layer-final"] .price_layer').html(currency_symbol+parseFloat(price).toFixed(2));
        },
        removeItemFromLayer: function(el){
            var canvas = pdc.getCurrentCanvas();
            var objects = canvas.getObjects();
            if((el!='')&&(el!=undefined)){
                for (var i = 0; i < objects.length; i++) {
                    if(objects[i].name==el){
                        canvas.remove(objects[i]);
                        PDC_layer.update_price();
                    }
                }
            }
        },
        updatePosition: function(){
            $('[pdc-layer]').each(function(){
                $(this).attr('pdc-layer',$(this).index());
            })
        }
    }
    PDC_layer.init();
})