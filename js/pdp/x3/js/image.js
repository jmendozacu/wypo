var x3Image = jQuery.noConflict();
x3Image(function($) {
    var baseUrl = $("#base_url").val(),
        mediaUrl = $("#pdp_media_url").val(),
        _productConfig = pdc.getProductConfig(),
        _uploadConfig = '';
    if($("#pdc_upload_config").length) {
        _uploadConfig = JSON.parse($("#pdc_upload_config").val());
    }
    //UPLOAD IMAGE USING DROPZONE JS
    if($("div.pdc-upload-area").length) {
        Dropzone.autoDiscover = false;
        //************************* UPLOAD Image Dropzone *************************
        var uploadImageUrl = pdc.getBaseUrl() + "pdp/upload/uploadImage";
        $("div.pdc-upload-area").dropzone({ 
            url: uploadImageUrl,
            uploadMultiple: false,
            paramName: 'filename',
            maxFiles: _uploadConfig.upload_max_files || 10,
            parallelUploads: _uploadConfig.upload_max_files || 10,
            //maxFilesize: _uploadConfig.upload_max_size || 5,
            autoQueue: true,
            acceptedFiles: $('#upload_accept_files').val() || 'image/*',
            autoProcessQueue: true,
            dictDefaultMessage: _uploadConfig.default_message || 'DROP FILES HERE OR CLICK TO UPLOAD',
            addRemoveLinks: true,
            accept: function(file, done) {
                done();
                /**
                file.acceptDimensions = done;
                file.rejectDimensions = function() { 
                    var errorMessage = _uploadConfig.upload_min_pixel_error || "This image is too small. Please use a larger image!";
                    done(errorMessage); 
                };**/
            },
            dictCancelUpload: _uploadConfig.cancel_label || "Cancel",
            dictRemoveFile: _uploadConfig.remove_label || "Remove",
            filesizeBase: 1024,
            dictFileTooBig: _uploadConfig.upload_max_size_message || "This file too big",
            dictMaxFilesExceeded: _uploadConfig.upload_max_files_message || "Max files exceeded. Please remove all and upload again!",
        });
        var uploadImageDropzone = Dropzone.forElement(".pdc-upload-area");
        if(_uploadConfig.upload_max_size) {
            uploadImageDropzone.options.maxFilesize = _uploadConfig.upload_max_size;
        }
        uploadImageDropzone.on("addedfile", function(file) {
            /* Maybe display some more file information on your page */		
        });
        /**
        //Check image dimensions before upload
        uploadImageDropzone.on("thumbnail", function(file) {
            //Reject images based on image dimensions
            if(_uploadConfig.upload_min_pixel_width || _uploadConfig.upload_min_pixel_height) {
                var minWidth = _uploadConfig.upload_min_pixel_width,
                    minHeight = _uploadConfig.upload_min_pixel_height;
                if (file.width < minWidth || file.height < minHeight) {
                    file.rejectDimensions()
                } else {
                    file.acceptDimensions();
                }
            } else {
                file.acceptDimensions();
            }
            
        });**/
        uploadImageDropzone.on("error", function(file) {
            console.warn("Dropzone, something wrong here");		
        });
        uploadImageDropzone.on("sending", function(file, xhr, formData) {
            // Will send the filesize along with the file as POST data.
            formData.append("product_id", $("#current_product_id").val());
        });
        uploadImageDropzone.on("removedfile", function(file) {
            if($(".pdc-upload-area .dz-preview:visible").length == 0) {
                //$(".pdc-upload-area .dz-default.dz-message").show();
                try {
                    uploadImageDropzone.removeAllFiles();     
                } catch(error) {
                    console.warn("Empty dropzone anyway");
                }
                
            }		
        });
        uploadImageDropzone.on("complete", function(file) {
            //console.info("Upload file completed", file);
            /*$('.gallery-items').imagelistexpander({
                prefix: "gallery-"
            });*/
        });
        uploadImageDropzone.on("success", function(file, response) {
            try {
                var responseJson = JSON.parse(response);
                if(responseJson.status == "success") {
                    var validImage = true,
                        _checkImage = responseJson.check_status,
                        imgClass = 'valid';
                    if(_checkImage.min_dpi_enable && !_checkImage.valid_image) {
                        validImage = false;
                        imgClass = 'warming';
                    }
                    if(_checkImage.image_dimension && !_checkImage.image_dimension.valid_image) {
                        validImage = false;
                        imgClass = 'warming';
                    }
                    var imgItem = '<li class="'+ imgClass +'"><span class="price">'+ (responseJson.price_format || "Free") +'</span> <div class="del-upload-image-btn"><span title="Delete">x</span></div><a class="uploaded-img"><img object-type="upload_image" rel="'+ responseJson.filename +'" src="'+ (responseJson.thumbnail || responseJson.filename) +'" price="'+ responseJson.price +'"></a></li>';
                    $("#upload ul.items-list").prepend(imgItem);
                    $(".pdc-upload-area .dz-success").each(function() {
                        $(this).hide();
                    });
                    //If all uploaded successfully, then reset dropzone
                    if($(".dz-preview.dz-error").length == 0) {
                        uploadImageDropzone.removeAllFiles();   
                    }
                    //If there is invalid image, then show the message
                    if(!validImage) {
                        $('[pdc-data="image-warming"]').show();    
                    }
                } else {
                    alert(responseJson.message);
                }
            } catch(error) {
                console.warn(error);
                alert("Something went wrong. Can not upload image to server!");
            }
            //uploadImageDropzone.removeFile(file);
        });
        $('[pdc-action="upload-image"]').click(function() {
            uploadImageDropzone.processQueue();
        });
        $(document).on('click', '[pdc-action="add-upload-image"]', function() {
            var options = {
                object_type: 'uploaded_image',
                price: pdc.getProductConfig().clipart_price
            }
            pdc.addImage($(this).attr("rel"), options);
        });
    }
	//************************* END UPLOAD IMAGE Dropzone *************************
    PDCImage = {
        init : function() {
			this.initImages();
			this.filterByCategory();
            this.getImageOfFirstCategory();
            this.loadMoreImage();
		},
        showLoading : function() {
            //$(".pdploading").show();
            if(window.Pace !== undefined) {
                Pace.restart();
            }
        },
        hideLoading : function() {
            $(".pdploading").hide();
        },
        itemPerRow : 3,
        limit: $("#default_page_size").val() || 12,
        initImages: function() {
            this.appendCategoriesToDesign();  
        },
        getImageCategories: function() {
            if($("#pdc-image-categories").val()) {
                return JSON.parse($("#pdc-image-categories").val());
            }
        },
        appendCategoriesToDesign: function() {
            var categories = this.getImageCategories(),
                selectedCategories = pdc.getProductImageCategoryIds();
            if(categories) {
                var optionHtml;
                $.each(categories, function(index, _category) {
                    //Check if this category selected for this product or not
                    if($.inArray(_category.id, selectedCategories) == -1) return;
                    optionHtml = '<option value="'+ _category.id +'" type="'+ _category.image_types +'">'+ _category.title +'</option>';
                    var container = $('[pdc-image-type="'+ _category.image_types +'"]');
                    container.find('[pdc-data="pdc-image-category"]').append(optionHtml);
                    container.find('.image-container').show();
                    container.find('.no-item').hide();
                });
                //Hide if clipart, frame, image, background ... only have 1 category
                $('[pdc-data="pdc-image-category"]').each(function() {
                    if($(this).find("option").length == 1) {
                        $(this).closest(".select-cont").hide();
                    }
                });
            }
        },
		loadMoreImage : function() {
			var selectedOption,
                imageTypeContainer,
                imageType,
                self = this;
            $(document).on('click', '[pdc-data="load-more-image"]', function() {
                imageTypeContainer = $(this).closest('[pdc-image-type]');
                selectedOption = imageTypeContainer.find("[pdc-data='pdc-image-category'] option:selected");
                if(!selectedOption || !imageTypeContainer) return;
                var currentPage = selectedOption.attr("cr_act"),
                    category = selectedOption.val(),
                    pageSize = PDCImage.limit;
                self.getImages(category, pageSize, currentPage, '', imageTypeContainer, function(response, imageTypeContainer) {
                    var _returnItems = 0;
                    if (response != "nomore") {
                        imageType = imageTypeContainer.attr("pdc-image-type");
                        var data = $.parseJSON(response),
                            item = "",
                            itemName = "",
                            imgFile = "",
                            imgThumbnail = "";
                        for (var i = 0; i < data.length; i++) {
                            if(data[i].image_name != "" && data[i].image_name != null) {
                                itemName = data[i].image_name;
                            }
                            imgThumbnail = mediaUrl + 'artworks/' + (data[i].thumbnail || data[i].filename);
                            imgFile = mediaUrl + 'artworks/' + data[i].filename;
                            //Add background color for background image - need for pattern
                            var bgColorStyle = '';
                            if(imageType == "background") {
                                if($(".pdc-background-color-list li.active a").length) {
                                    var activeColor = $(".pdc-background-color-list li.active a").css('background-color');
                                    bgColorStyle = 'style="background: '+ activeColor +' url(\''+ imgThumbnail +'\')"';
                                } else {
                                    bgColorStyle = 'style="background:#808080 url(\'' + imgThumbnail + '\') repeat scroll center center;"';
                                }
                                item += "<li cat='"+ data[i].category +"'> <span class='price'>"+ data[i].price_format +"</span><a object-type='"+ imageType 
                                + "' rel='"+ imgFile +"' "+ bgColorStyle +" class='select-img' id='img" + data[i].image_id + "' price='"+ data[i].price+"' title='" 
                                + itemName +"'></a> </li>";
                            } else {
                                item += "<li cat='"+ data[i].category +"'><span class='price'>"+ data[i].price_format +"</span> <a "+ bgColorStyle +" class='select-img'><img object-type='"+ imageType 
                                + "' rel='"+ imgFile +"' src='" + imgThumbnail +"' id='img" + data[i].image_id + "' price='"+ data[i].price+"' title='" 
                                + itemName +"' /></a> </li>";    
                            }
                        }
                        imageTypeContainer.find('.no-image-item').hide();
                        imageTypeContainer.find('[pdc-data="image-list"]').append(item);
                        //Increment current page by 1
                        var currentPage = imageTypeContainer.find("[pdc-data='pdc-image-category'] option:selected").attr("cr_act");
                        imageTypeContainer.find("[pdc-data='pdc-image-category'] option:selected").attr("cr_act", parseInt(currentPage) + 1);
                        _returnItems = data.length;  
                        PDCImage.initScrollbar();
                    }
                     PDCImage.showOrHideLoadMoreBtn(_returnItems, imageTypeContainer);
                });
            });
		},
        getImages: function(category, pageSize, currentPage, includeIds, imageTypeContainer, callback) {
            $.ajax({
                type : "POST",
                url : baseUrl + "pdp/index/loadMoreImage",
                data : {
                    current_page : currentPage, 
                    category : category, 
                    page_size : pageSize || PDCImage.limit,
                    ids: includeIds || ''
                },
                beforeSend : function() {
                    PDCImage.showLoading();
                },
                error : function() {

                }, 
                success : function(response) {
                    callback && callback(response, imageTypeContainer);
                    PDCImage.hideLoading();
                }
            });
        },
		filterByCategory : function() {
            var selectedOption,
                imageTypeContainer,
                imageType,
                self = this;
            $(document).on('change', '[pdc-data="pdc-image-category"]', function() {
                selectedOption = $($(this).find("option:selected"));
                imageTypeContainer = $(this).closest('[pdc-image-type]');
                if(!selectedOption || !imageTypeContainer) return;
                imageTypeContainer.find('[pdc-data="image-list"] li[cat!='+ selectedOption.attr("value") +']').hide();
                imageTypeContainer.find('[pdc-data="image-list"] li[cat='+ selectedOption.attr("value") +']').show();
                imageTypeContainer.find('[pdc-data="image-list"] li[rel="no-frame"]').show();
                if(!selectedOption.hasClass("cat_loaded")){
                    selectedOption.addClass("cat_loaded");
                    var currentPage = 1,
                        category = $(this).val(),
				        pageSize = PDCImage.limit;
                    self.getImages(category, pageSize, currentPage, '', imageTypeContainer, function(response, imageTypeContainer) {
                        var _returnItems = 0;
                        if (response != "nomore") {
                            imageType = imageTypeContainer.attr("pdc-image-type");
                            var data = $.parseJSON(response),
                                item = "",
                                itemName = "",
                                imgFile = "",
                                imgThumbnail = "";
                            for (var i = 0; i < data.length; i++) {
                                if(data[i].image_name != "" && data[i].image_name != null) {
                                    itemName = data[i].image_name;
                                }
                                imgThumbnail = mediaUrl + 'artworks/' + (data[i].thumbnail || data[i].filename);
                                imgFile = mediaUrl + 'artworks/' + data[i].filename;
                                //Add background color for background image - need for pattern
                                var bgColorStyle = '';
                                if(imageType == "background") {
                                    if($(".pdc-background-color-list li.active a").length) {
                                        var activeColor = $(".pdc-background-color-list li.active a").css('background-color');
                                        bgColorStyle = 'style="background: '+ activeColor +' url(\''+ imgThumbnail +'\')"';
                                    } else {
                                        bgColorStyle = 'style="background:#808080 url(\'' + imgThumbnail + '\') repeat scroll center center;"';
                                    }
                                    item += "<li cat='"+ data[i].category +"'> <span class='price'>"+ data[i].price_format +"</span><a object-type='"+ imageType 
                                    + "' rel='"+ imgFile +"' "+ bgColorStyle +" class='select-img' id='img" + data[i].image_id + "' price='"+ data[i].price+"' title='" 
                                    + itemName +"'></a> </li>";
                                } else {
                                    item += "<li cat='"+ data[i].category +"'> <span class='price'>"+ data[i].price_format +"</span><a "+ bgColorStyle +" class='select-img'><img object-type='"+ imageType 
                                    + "' rel='"+ imgFile +"' src='" + imgThumbnail +"' id='img" + data[i].image_id + "' price='"+ data[i].price+"' title='" 
                                    + itemName +"' /></a> </li>";    
                                }
                            }
                            imageTypeContainer.find('.no-image-item').hide();
                            imageTypeContainer.find('[pdc-data="image-list"]').append(item);
                            imageTypeContainer.find("[pdc-data='pdc-image-category'] option:selected").attr("cr_act", 2);
                            _returnItems = data.length;
                            PDCImage.initScrollbar();
                            //PDCImage.randomSelectBackgroundColor(imageType);
                            PDCImage.showOrHideLoadMoreBtn(data.length, imageTypeContainer);
                        } else {
                            imageTypeContainer.find('.no-image-item').show();
                        }
                    });
                } else {
                    PDCImage.showOrHideLoadMoreBtn(imageTypeContainer.find('[pdc-data="image-list"] li:visible').length, imageTypeContainer);
                    if(!imageTypeContainer.find('[pdc-data="image-list"] li:visible').length) {
                        imageTypeContainer.find('.no-image-item').show();
                    } else {
                        imageTypeContainer.find('.no-image-item').hide();
                    }
                }
            });
		},
        initScrollbar: function(selector) {
            $(".items-list").scrollbar({
                //"showArrows": true,
                //"scrollx": "simple",
                "scrolly": "simple"
            });  
        },
        showOrHideLoadMoreBtn : function(_returnItems, imageTypeContainer) {
            if(_returnItems >= PDCImage.limit) {
                //Check the last image has last-image class or not
                var _lastImage = false;
                if(imageTypeContainer.find('[pdc-data="image-list"] li:visible:last').hasClass("last-image")) {
                    _lastImage = true;
                }
                if(_lastImage) return;
                imageTypeContainer.find('[pdc-data="load-more-image"]').show();
            } else {
                imageTypeContainer.find('[pdc-data="load-more-image"]').hide();
                //Add last-item class for last image
                imageTypeContainer.find('[pdc-data="image-list"] li:visible:last').addClass("last-image");
            }
        },
        getImageOfFirstCategory: function() {
            $('[pdc-data="pdc-image-category"]').change();
            //$('[pdc-image-type="clipart"] [pdc-data="pdc-image-category"]').change();
        },
        clickImage: function() {
            $(document).on('click', '[pdc-data="image-list"] img', function() {
                var options = {};
                options.object_type = $(this).attr("object-type") || "";
                if($(this).attr("price")) {
                    options.price = $(this).attr("price");
                }
                //Active class
                $('[pdc-image-type="'+ options.object_type +'"] .items-list li').removeClass("active");
                $(this).closest("li").addClass("active");
                switch(options.object_type) {
                    case 'image':
                    case 'shape':
                    case 'clipart':
                        pdc.addImage($(this).attr("rel"), options);
                        break;
                    case 'frame':
                        if($(this).closest('li').attr("rel") == "no-frame") {
                            pdc.removeFrameFromDesign();   
                        } else {
                            pdc.addFrameToDesign($(this).attr("rel"), options);    
                        }
                        break;
                    //case 'background':
                        //pdc.addBackgroundLayer($(this).attr("rel"), options);
                        //break;
                    case 'upload_image':
                        options.price = pdc.getProductConfig().clipart_price || 0
                        pdc.addImage($(this).attr("rel"), options);
                        break;
                }
              
            });  
        }(),
        //Background has different html format
        clickBackground: function() {
            $(document).on('click', '[pdc-image-type="background"] [pdc-data="image-list"] a', function() {
                var options = {};
                options.object_type = $(this).attr("object-type") || "";
                if($(this).attr("price")) {
                    options.price = $(this).attr("price");
                }
                //Active class
                $('[pdc-image-type="background"] .items-list li').removeClass("active");
                $(this).closest("li").addClass("active");
                pdc.addBackgroundLayer($(this).attr("rel"), options);
            });
        }(),
        randomSelectBackgroundColor: function(objectType) {
            if(objectType && objectType != "background") return;
            var colorList = $('[pdc-data="CHANGE_BG_COLOR"] li').length;
            if(colorList) {
                var random = this.getRandomIntInclusive(1, colorList);
                console.info(random, $('[pdc-data="CHANGE_BG_COLOR"] li:nth-child('+ random +')'));
                if($('[pdc-data="CHANGE_BG_COLOR"] li:nth-child('+ random +')')) {
                    $('[pdc-data="CHANGE_BG_COLOR"] li:nth-child('+ random +')').click();    
                }
            }
        },
        getRandomIntInclusive: function(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
		copyImageToServer: function(url, productId, callback) {
			var copyActionUrl = baseUrl + "pdp/upload/copyImageFromUrl";
			pdc.doRequest(copyActionUrl, {url: url, product_id: productId}, function(response) {
                callback && callback(response);
            });
		},
        removeUploadImage: function() {
            $(document).on("click", '.del-upload-image-btn', function() {
                if(confirm("Are you sure?")) {
                    var removeImg = $(this).closest("li").find("img").attr("rel"),
                        removeUrl = baseUrl + "pdp/upload/removeImage?image-path=" + removeImg;
                    if(removeImg) {
                        $.get(removeUrl, function(response) {
                            var responseInJson = JSON.parse(response);
                            if(responseInJson.status == "success") {
                                if($('[pdc-image-type="upload_image"] img[rel="'+ responseInJson.filename +'"]').length) {
                                    $('[pdc-image-type="upload_image"] img[rel="'+ responseInJson.filename +'"]').closest("li").remove();
                                }
                            } else {
                                alert(responseInJson.message);
                            }
                        });
                    }
                    
                }
            }); 
        }()
	}
    PDCImage.init();
    //QRCODE PLUGIN ==================
    PDCQRCode = {
        defaultConfig: {
            chs: '200x200',
            cht: 'qr',
            originalImgUrl: 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=Hello World!'
        },
        createQRCodeEvent: function() {
            $('.gene-qrcode-btn').click(function() {
				console.info("Create qr code clicked");
                var activeElement = $(".qrcode-type.active"),
                    content = activeElement.find("input").val() || "";
                if (content) {
                    PDCQRCode.createQRCodeOnServer(content, function(response) {
                        console.info(response);
                        var responseJson = JSON.parse(response);
                        if(responseJson.status == "success") {
                            var qrcodeImgUrl = responseJson.filename,
                            qrcodePreviewTarget = $('.qrcode-type.active [pdc-data="qrcode-result"]');
                            //Add to design after created
                            pdc.addImage(responseJson.filename, {
                               object_type: 'clipart',
                            });
                            return false;
                            //Replace qrcode base image
                            qrcodePreviewTarget.attr("src", qrcodeImgUrl).css({
                                "opacity": 0.2
                            });
                           qrcodePreviewTarget.load(function() {
                                $(".qrcode-type.active .qrcode-overlay").css({
                                    "visibility": "hidden"
                                });
                                qrcodePreviewTarget.css({
                                    "opacity": 1
                                });
                            }); 
                        } else {
                            alert(responseJson.message);
                        }
                        pdc.hideLoadingBar();
                    });
                }
            });
        }(),
        createQRCode: function(content) {
            //https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=DucTuan
            var qrGoogleURL = "https://chart.googleapis.com/chart?",
                content = content || '',
                imageUrl = qrGoogleURL + "cht=" + this.defaultConfig.cht + "&chs=" + this.defaultConfig.chs + "&chl=" + content;
            return imageUrl;
        },
        createQRCodeOnServer: function(content, callback) {
            var createQRCodeURL = baseUrl + "pdp/upload/createQRCodeOnServer";
            pdc.doRequest(createQRCodeURL, {content: content}, function(response) {
                callback && callback(response);
            });
        },
        addQrcodeToDesignEvent: function() {
            $('.qrcode-type .add-design-btn').click(function() {
                $('.qrcode-type.active [pdc-data="qrcode-result"]').click();
            });
        }(),
        clickQRCodeImage: function() {
            $('[pdc-data="qrcode-result"]').click(function() {
                pdc.addImage($(this).attr("src"), {object_type: 'qrcode'});
            });
        }()
    }
    //END QRCODE PLUGIN ==================
    
    //Design Template List
    PDCTemplate = {
        init : function() {
			if($('[pdc-data="pdp-templates"]').length) {
                this.getDesignTemplate(1);
            }
		},
        showLoading : function() {
            //$(".pdploading").show();
            if(window.Pace !== undefined) {
                Pace.restart();
            }
        },
        hideLoading : function() {
            $(".pdploading").hide();
        },
        itemPerRow : 3,
        limit: 12,
		getDesignTemplate : function(currentPage) {
			$.ajax({
                type : "POST",
                url : baseUrl + "pdp/template/getDesignTemplate",
                data : {
                    current_page : currentPage,
                    product_id: pdc.getCurrentProductId()
                },
                beforeSend : function() {
                    PDCTemplate.showLoading();
                },
                error : function() {
                    alert("Can not load product design template");
                }, 
                success : function(response) {
                    try {
                        if(response != "nomore") {
                            var responseInJSON = JSON.parse(response),
                                _templateListHtml = '';
                            $.each(responseInJSON, function(i, _template) {
                                //console.info(i, _template);
                                _templateListHtml += '<li pdc-design="'+ _template.pdp_design +'">';
                                    _templateListHtml += '<a title="'+ _template.template_name +'" href="javascript:void(0)"><img src="'+ mediaUrl + 'thumbnail/' + _template.template_thumbnail +'"/></a>';
                                _templateListHtml += '</li>';
                            });
                            $('[pdc-data="pdp-templates"] ul.items-list').append(_templateListHtml);
                        }
                        PDCTemplate.showOrHideLoadMoreBtn(response);
                    } catch (error) {
                        alert("Something went wrong! Can not get design template");
                        console.error(error);
                    }
                    PDCTemplate.hideLoading();
                }
            });
		},
        initScrollbar: function(selector) {
            $(".items-list").scrollbar({
                //"showArrows": true,
                //"scrollx": "simple",
                "scrolly": "simple"
            });  
        },
        showOrHideLoadMoreBtn : function(response) {
            var _responseItem = 0;
            if(response != "nomore") {
                //Count how many item from response
                var responseInJson = JSON.parse(response);
                _responseItem = responseInJson.length;
            }
            if(_responseItem >= PDCTemplate.limit) {
                var loadMoreBtn = $('[pdc-data="load-more-template"]');
                var currentPage = parseInt(loadMoreBtn.attr("data-current-page"));
                loadMoreBtn.attr("data-current-page", ++currentPage);
                loadMoreBtn.show(); 
            } else {
                $('[pdc-data="load-more-template"]').hide();
            }
        },
        getDesignContent: function(jsonFilename, callback) {
            $.ajax({
                type : "POST",
                url : baseUrl + "pdp/template/getDesignContent",
                data : {
                    json_filename : jsonFilename
                },
                beforeSend : function() {
                    PDCTemplate.showLoading();
                },
                error : function() {
                    alert("Can not load this design template");
                }, 
                success : function(response) {
                    try {
                        if(response != "") {
                            callback && callback(response);
                        } else {
                            alert("Sorry. This template not working properly!");
                        }
                    } catch (error) {
                        alert("Something went wrong! Can not get design template");
                        console.error(error);
                    }
                    PDCTemplate.hideLoading();
                }
            });
        },
        selectDesignTemplate: function() {
            $(document).on("click",'[pdc-data="pdp-templates"] ul.items-list li' ,function() {
                PDCTemplate.getDesignContent($(this).attr("pdc-design"), function(response) {
                    pdc.changeDesignTemplate($(this).attr("pdc-design"), response);
                });
            });
        }(),
        clickLoadMoreBtn: function() {
            $('[pdc-data="load-more-template"]').click(function() {
                PDCTemplate.getDesignTemplate($(this).attr("data-current-page"));
            });
        }()
	}
    PDCTemplate.init();
    //End Design Template List
    
    //===============Facebook and Instagram Image ======================
	$(document).on("click", "#pdc_instagram_list_img img, #photos_album img", function() {
		var options = {
			object_type : "clipart"
		};
		PDCImage.copyImageToServer($(this).attr("src"), _productConfig.product_id, function(response) {
			var responseInJSON = JSON.parse(response);
			if(responseInJSON.status == "success") {
				$('img[src="'+ responseInJSON.original_filename +'"]').attr("src", responseInJSON.filename);
				//Add item to design
				pdc.addImage(responseInJSON.filename, {
					object_type: "clipart"
				});
			} else {
				alert(responseInJSON.message)
			}
			PDCImage.hideLoading();
		});
	});
	//===============End Facebook and Instagram Image ======================
});