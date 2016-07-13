var mst = jQuery.noConflict();
mst(document).ready(function($) {
        pdc = PDC();
        allCanvas = {};
    var baseUrl = $("#base_url").val(),
        pdcMediaUrl = $("#pdp_media_url").val(),
		defaultRenderTime = 1000;
	var isServerNginx = ($("#server-nginx").length) ? $("#server-nginx").val() : 0,
	PDCExport = {
        //Canvas background before exclude
        canvasOriginalBackground: {},
        canvasOriginalOverlay: null,
		init : function() {
			PDCExport.renderToCanvas();
			setTimeout(function() {
				$.each(allCanvas, function() {
					this.renderAll();
					
				});
			}, defaultRenderTime);
			//this.addControls();
		},
        getDesignJson : function() {
            var jsonContent = $("#final_design_json").val();
            return JSON.parse(jsonContent);
        },
		renderToCanvas : function() {
            pdc.showLog("Load From Json", "info");
            var self = this,
                sides = self.getDesignJson();
            $.each(sides, function() {
                var sideInfo = this;
                var canvasId = "canvas_side_" + sideInfo.id;
                allCanvas[canvasId] = new fabric.Canvas(canvasId);
                var _validJson = pdc.checkImagePathInJson(sideInfo.json);
                if(!_validJson) return;
                allCanvas[canvasId].loadFromJSON(_validJson, allCanvas[canvasId].renderAll.bind(allCanvas[canvasId]), function(o, object) {
                    object.set({
                        selectable: false
                    });
                    //Remove shadow if not setting, prevent duplicate text while export svg
                    if (object.shadow !== null && object.shadow !== "") {
                        if (parseInt(object.shadow.toObject().offsetX) == 0 
                            && parseInt(object.shadow.toObject().offsetY) == 0
                            && object.shadow.toObject().color == "#FFFFFF") {
                            object.set({
                                shadow: null
                            });
                        }
                    }
                });
                allCanvas[canvasId].side_name = sideInfo.side_name;
            });
		},
        exportOption: function() {
            pdc.showLog("Active Export Options Events", "info");
            $('[pdc-data="export-option"]').click(function() {
                if($(this).hasClass("active")) return;
                var _optionName = $(this).find("input").attr("name"),
                    _optionValue = $(this).find("input").attr("value");
                switch(_optionName) {
                    case 'include_background':
                        PDCExport.toggleIncludeBackground(_optionValue);
                        break;
                    case 'include_overlay':
                        PDCExport.toggleIncludeOverlay(_optionValue);
                        break;    
                    case 'edit_design':
                        PDCExport.toggleEditDesign(_optionValue);
                        break;
                }
            });
        }(),
        toggleIncludeBackground: function(isInclude) {
            var self = this;
            var _canvas = self.getActiveCanvas();
            if(isInclude === "1") {
                _canvas.forEachObject(function(object){
                    console.info(object);
                    if(object.object_type && (object.object_type == "background" || object.object_type == "background_color")) {
                        object.visible = true;
                    }
                });
                _canvas.renderAll();
            } else {
                _canvas.forEachObject(function(object){
                    if(object.object_type && (object.object_type == "background" || object.object_type == "background_color")) {
                        object.visible = false;
                    }
                });
                _canvas.renderAll();
            }
        },
        toggleIncludeOverlay: function(isInclude) {
            var self = this;
            var _canvas = self.getActiveCanvas();
            if(isInclude === "1") {
                pdc.showLog("Include overlay request", "info");
                if(self.canvasOriginalOverlay) {
                    _canvas.setOverlayImage(self.canvasOriginalOverlay, _canvas.renderAll.bind(_canvas));
                }
            } else {
                pdc.showLog("Exclude overlay request", "info");
                self.canvasOriginalOverlay = _canvas.overlayImage;
                _canvas.setOverlayImage(null, _canvas.renderAll.bind(_canvas));
                //_canvas.setOverlayColor(null, _canvas.renderAll.bind(_canvas));
            }
        },
        toggleEditDesign: function(isEditable) {
            var self = this;
             var _canvas = self.getActiveCanvas();
            if(isEditable === "1") {
                pdc.showLog("Edit design request", "info");
                _canvas.forEachObject(function(object){
                    //Don't allow edit background layer
                    if(object.object_type && (object.object_type == "background" || object.object_type == "background_color")) {
                        return;
                    }
                    object.set({
                        selectable: true
                    });
                });
            } else {
                _canvas.forEachObject(function(object){
                    object.set({
                        selectable: false
                    });
                });
                _canvas.deactivateAll().renderAll();
            }
        },
        getActiveCanvas: function() {
            var _activeCanvasId = "canvas_" + $("#canvas_list li.active a").attr("aria-controls");
            return allCanvas[_activeCanvasId];
        },
        downloadPng: function(format) {
            var self = this,
                canvasDataURL = self.getActiveCanvas().toDataURL({
                    format: format || 'png', 
                    multiplier: 1,
                }),
                savePngUrl = baseUrl + 'pdp/index/saveBase64ImageExport';
            pdc.doRequest(savePngUrl, {
                base_code_image: canvasDataURL,
                order_info: self.getOrderInfo(),
                format: format || 'png'
            }, function(response) {
                pdc.showLog("The png file response to download png event", "info");
                var responseJson = JSON.parse(response);
                if(responseJson.status === "success") {
                    pdc.hideLoadingBar();
                    // window.location = responseJson.thumbnail_path;
					if(isServerNginx == '1')
					{
						var pdcFileName = responseJson.thumbnail_path;
						var arPdfUrl = pdcFileName.split('/');
						var lengthArPdfUrl = arPdfUrl.length;
						lengthArPdfUrl = lengthArPdfUrl - 1;
						pdcFileName = arPdfUrl.slice(-1)[0] ;
						var baseDownloadAfter = $('#link-download-after').val();
						console.log(format);
						var typeImage = (format == 'jpg') ? format : 'png';
						baseDownloadAfter += '/type/'+typeImage+'/file-name/'+pdcFileName;
						$('a#pdc-show-link-down-link').attr('href',baseDownloadAfter);
						$.fancybox({
							href: '#pdc-show-link-down', 
							modal: false,
						});
					}
					else
					{
						window.location = responseJson.thumbnail_path;
					}
                    return false;
                }
                alert(responseJson.message);
            });
        },
        downloadSVG: function() {
            var self = this,
                canvasSvg = pdc.modifiedSvg(self.getActiveCanvas(), self.getActiveCanvas().getWidth(), self.getActiveCanvas().getHeight()) || pdc.setCurrentCanvas(self.getActiveCanvas()).canvas.toSVG(),
                saveSvgUrl = baseUrl + 'pdp/index/saveAndCreateSvg';
            pdc.doRequest(saveSvgUrl, {
                svg_string: canvasSvg,
                order_info: self.getOrderInfo()
            }, function(response) {
                pdc.showLog("The svg file response to download svg event", "info");
                var responseJson = JSON.parse(response);
                if(responseJson.status === "success") {
                    pdc.hideLoadingBar();
                    // window.location = responseJson.thumbnail_path;
					if(isServerNginx == '1')
					{
						var pdcFileName = responseJson.thumbnail_path;
						var arPdfUrl = pdcFileName.split('/');
						var lengthArPdfUrl = arPdfUrl.length;
						lengthArPdfUrl = lengthArPdfUrl - 1;
						pdcFileName = arPdfUrl.slice(-1)[0] ;
						var baseDownloadAfter = $('#link-download-after').val();
						baseDownloadAfter += '/type/svg/file-name/'+pdcFileName;
						$('a#pdc-show-link-down-link').attr('href',baseDownloadAfter);
						$.fancybox({
							href: '#pdc-show-link-down', 
							modal: false,
						});
					}
					else
					{
						window.location = responseJson.thumbnail_path;
					}
                    return false;
                }
                alert(responseJson.message);
            });
        },
        downloadPdf: function() {
            var self = this,
                canvasSvg = pdc.setCurrentCanvas(self.getActiveCanvas()).canvas.toSVG(),
                saveSvgUrl = baseUrl + 'pdp/index/createPdfFromSvg';
            pdc.doRequest(saveSvgUrl, {
                svg_string: canvasSvg,
                order_info: self.getOrderInfo()
            }, function(response) {
                pdc.showLog("The pdf file response to download pdf event", "info");
                var responseJson = JSON.parse(response);
                if(responseJson.status === "success") {
                    pdc.hideLoadingBar();
                    // window.location = responseJson.pdf_url;
					if(isServerNginx == '1')
					{
						var pdcFileName = responseJson.pdf_url;
						var arPdfUrl = pdcFileName.split('/');
						var lengthArPdfUrl = arPdfUrl.length;
						lengthArPdfUrl = lengthArPdfUrl - 1;
						pdcFileName = arPdfUrl.slice(-1)[0] ;
						var baseDownloadAfter = $('#link-download-after').val();
						baseDownloadAfter += '/type/pdf/file-name/'+pdcFileName;
						$('a#pdc-show-link-down-link').attr('href',baseDownloadAfter);
						$.fancybox({
							href: '#pdc-show-link-down', 
							modal: false,
						});
					}
					else
					{
						window.location = responseJson.pdf_url;
					}
                    return false;
                }
                alert(responseJson.message);
            });
        },
        downloadPdfFromPng: function() {
            var self = this,
                canvas = pdc.setCurrentCanvas(self.getActiveCanvas()).canvas,
                canvasBase64Code = canvas.toDataURL(),
                saveUrl = baseUrl + 'pdp/index/createPdfFromPng';
            pdc.doRequest(saveUrl, {
                png_string: canvasBase64Code,
                order_info: self.getOrderInfo()
            }, 
            function(response) {
                pdc.showLog("The pdf file response to download pdf event", "info");
                var responseJson = JSON.parse(response);
                if(responseJson.status === "success") {
                    pdc.hideLoadingBar();
                    // window.location = responseJson.pdf_url;
					if(isServerNginx == '1')
					{
						var pdcFileName = responseJson.pdf_url;
						var arPdfUrl = pdcFileName.split('/');
						var lengthArPdfUrl = arPdfUrl.length;
						lengthArPdfUrl = lengthArPdfUrl - 1;
						pdcFileName = arPdfUrl.slice(-1)[0] ;
						var baseDownloadAfter = $('#link-download-after').val();
						baseDownloadAfter += '/type/pdf/file-name/'+pdcFileName;
						$('a#pdc-show-link-down-link').attr('href',baseDownloadAfter);
						$.fancybox({
							href: '#pdc-show-link-down', 
							modal: false,
						});
					}
					else
					{
						window.location = responseJson.pdf_url;
					}
                    return false;
                }
                alert(responseJson.message);
            });
        },
        getOrderInfo: function() {
            return {
                'order_id': $("#order_id").val(),
                'item_id': $("#item_id").val(),
                'increment_id': $("#increment_id").val(),
                'product_id': $("#product_id").val(),
                'side_label': $("#canvas_list li.active a").text(),
                'json_filename': $("#json_filename").val()
            }
        },
        deferredRequest : function (url, data) {
            var def = $.Deferred();
            return $.ajax({
                type : "POST",
                url : url,
                data : data,
                beforeSend : function() {
                    $('.pdploading').show();
                    Pace.start();
                },
                error : function() {
                    console.log("Something went wrong...");
                }, 
                success : function(response) {
                    def.resolve();
                }
            });
            //return def.promise();
        },
        downloadAll: function() {
            if(allCanvas) {
                var self = this;
                console.info("Download all png, svg and pdf");
                var exportImageUrl = baseUrl + "pdp/export/saveExportImage";
                var allRequest = new Array();
                $.each(allCanvas, function(key, side) {
                    var sidePng = {
                        format: 'png',
                        base_code_image: side.toDataURL(),
                        side_name: side.side_name
                    };
                    var sideSvg = {
                        format: 'svg',
                        base_code_image: side.toSVG(),
                        side_name: side.side_name
                    };
                    allRequest.push(self.deferredRequest(exportImageUrl, sidePng));
                    allRequest.push(self.deferredRequest(exportImageUrl, sideSvg));
                });
                $.when.apply(null, allRequest)
                    .done(function() {
                        try {
                            var exportFiles = [];
                            $.each(arguments, function(index, response) {
                                if(response[0]) {
                                    var jsonData = JSON.parse(response[0]);
                                    //console.info(jsonData);
                                    exportFiles.push(jsonData.filename);
                                }
                            });
                            if(exportFiles) {
                                var downloadAllUrl = baseUrl + "pdp/export/downloadAll";
                                //Final request
                                pdc.doRequest(downloadAllUrl, {
                                    images: exportFiles.join(","),
                                    order_info: self.getOrderInfo()
                                }, function(response) {
                                    try {
                                        var downloadAllRes = JSON.parse(response);
                                        if(downloadAllRes.status == "success") {
                                            $(".pdploading").hide();
                                            // window.location.href = downloadAllRes.zip_url;
											if(isServerNginx == '1')
											{
												$('a#pdc-show-link-down-link').attr('href',downloadAllRes.zip_url);
												$.fancybox({
													href: '#pdc-show-link-down', 
													modal: false,
												});
											}
											else
											{
												window.location.href = downloadAllRes.zip_url;
											}
                                        } else {
                                            alert(downloadAllRes.message);
                                        }
                                    } catch(error) {
                                        alert(error);
                                    }
                                });
                            }
                        } catch(error) {
                            alert(error);
                        }
                    }).fail(function(error){
                        console.warn(error);
                        alert("Something went wrong! Can not download all at once. Please download them separately!");
                        //notify user of error
                    });
            }
        },
        exportBtnClickHandle: function() {
            var self = this;
            $('[pdc-data="pdc-export-btn"]').click(function() {
                var _action = $(this).attr("pdc-action");
                switch(_action) {
                    case 'DOWNLOAD_PDF_SVG' :
                        self.downloadPdf();
                        break;
                    case 'DOWNLOAD_PDF_PNG' :
                        self.downloadPdfFromPng();
                        break;    
                    case 'DOWNLOAD_SVG' :
                        self.downloadSVG();
                        break;
                    case 'DOWNLOAD_PNG' :
                        self.downloadPng();
                        break;
                    case 'DOWNLOAD_JPG' :
                        self.downloadPng('jpg');
                        break;    
                    case 'DOWNLOAD_ALL' : 
                        self.downloadAll();
                        break;
                }   
            });
        }
	}
	PDCExport.init();
    PDCExport.exportBtnClickHandle();
});