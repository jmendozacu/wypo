var mst = jQuery.noConflict();
mst(document).ready(function(){
    /* Hide the app content expanded as default of first load on small screen size device */
    if(mst(top.window).width()< 750){
        mst(".pdc-area-left .tab-content, .pdc-area-main").addClass('expand-main');
        mst(".pdc-area-left").addClass('collapse-left');
    }

    /* Button collapse the app content */
    mst("#toggle-app-button").click(function(){
        mst(".pdc-area-left .tab-content, .pdc-area-main").addClass('expand-main');
        mst(".pdc-area-left").addClass('collapse-left');
		mst(".pdc-area-left .pdc-tabs ul.tabs-left li").removeClass("active");
    });
	if(mst(top.window).width()< 750){
		mst(".pdc-area-main").click(function(){ 
			mst(".pdc-area-left .tab-content, .pdc-area-main").addClass('expand-main');
			mst(".pdc-area-left").addClass('collapse-left');
			mst(".pdc-area-left .pdc-tabs ul.tabs-left li").removeClass("active");
		});
	 }
    /* Expanded the app content by click on the left app */
    mst(".pdc-area-left .pdc-tabs > ul.nav > li > a").click(function(){
        mst(".pdc-area-left .tab-content, .pdc-area-main").removeClass('expand-main');
        mst(".pdc-area-left").removeClass('collapse-left');
    });	

    //End
    //Fancybox
    mst('.fancybox').fancybox({
        closeBtn   : false,
        title      :false
    });
    mst(".pdc-close-popup").click(function(){
        mst.fancybox.close({});
    });
    //
    // imagelistexpander
    /* mst('.gallery-items').imagelistexpander({
            prefix: "gallery-"
        }); */
    mst(".pdc-item-tool ul").hide();
    mst(".pdc-item-tool > h3").click(function(event) {
        event.stopPropagation();
        mst(this).next().addClass('current').toggle();
        ObjEvents.hide_popup_block();
    });	

    mst(".pdc-fonts-size ul li a").click(function() {
        var text = mst(this).html();
        mst(".pdc-fonts-size h3 span").html(text);
        mst(".pdc-item-tool ul").hide();
        ObjEvents.editText('fontSize',text);
    });	
    mst(".pdc-fonts-family ul li a").click(function() {
        var text = mst(this).html();
        ObjEvents.editText();
        if(text.length > 9) {
            text = text.substring(0,9)+' ...';
        }
        mst(".pdc-fonts-family h3 span").html(text);
        mst(".pdc-item-tool ul").hide();
    });					
    mst(document).click( function(){			
        //mst(".pdc-item-tool > ul").hide();
    });
    //
    //
    mst(".pdc-area-left .tab-content .tab-pane > ul > li").click(function(){
        if ( mst(this).hasClass("active") ) {
            mst(".pdc-area-left .tab-content .tab-pane > ul > li").addClass('opacity');
        }else{
            mst(".pdc-area-left .tab-content .tab-pane > ul > li").removeClass('opacity');
        }
    });
    mst(".pdc-area-left .tab-content .tab-pane > ul > li").hover(
        function(){
            mst(this).addClass('over');
            mst(".pdc-area-left .tab-content .tab-pane > ul > li").addClass('opacity');
        }, 
        function(){
            mst(this).removeClass('over');
            mst(".pdc-area-left .tab-content .tab-pane > ul > li").removeClass('opacity');
        }
    );
    // tab on QRcode
    mst('.pdc-show-content-detail .nav-tabs a').click(function (e) {
      e.preventDefault()
      mst(this).tab('show')
    });
    mst(".pdc-toolbar ul li").click(function(){
        if ( mst(".pdc-toolbar .tab-content > .tab-pane").hasClass("active") ) {
            mst(".pdc-scroll").addClass('expanded');
        }else{
            mst(".pdc-scroll").removeClass('expanded');
        }
    });
	// Toggle Sides Tab
	mst('.sides-tab span.icon').click(function (e) {
      e.preventDefault();
      mst(this).prev().slideToggle("fast");
      mst(this).parent().next().slideToggle("fast");
    });
	//Tooltip
	 mst("body").tooltip({ selector: '[data-x3=tooltip]' });
	
	// Toggle FullScreen
	mst('.topbar-content .show-full-screen').click(function (e) {
		e.preventDefault();
		mst("body").toggleClass("small-popup");	  
      /* mst(this).toggleClass("");	 */  
    });	
	
	/* If only one tab in Product Design then hide the tab button */
	/* if ( mst('#p-design-tab ul.nav-tabs li').length <2 ) { mst('#p-design-tab').addClass('hide-tab-btn');
	console.log('Tab length:'+mst('#p-design-tab ul.nav-tabs li').length);
	} */
	mst('#accordion_design .panel:first-child .panel-title > a').removeClass('collapsed');
	mst('#accordion_design .panel:first-child .panel-collapse').addClass('in');

});
