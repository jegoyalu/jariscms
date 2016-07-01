$.fn.dropdown = function(options) {
    var defaults = {
    	hideSpeed: 50,
    	showSpeed: 350,
    	parentBGHoverColor: false,
    	parentTextHoverColor: false,
    	zIndex: 5000
    };

    var settings = $.extend({}, defaults, options);

    this.each(function() {
        $(this).find("li > ul").css("display", "none");

        $(this).find("li > ul").parent().hover(
    	    function(){
    		    show(this);
    	    },

    	    function(){
    		    hide(this);
    	    }
    	);
    });

    function show(menu){
    	if($(menu).children("li > ul").css("display") == "none"){

    	    $(menu).attr("showing", "1");

    	    if(settings.parentBGHoverColor){
        		if(!$(menu).attr("dropdownParentOriginalBGColor")){$(menu).attr("dropdownParentOriginalBGColor", $(menu).css("background-color"));}
        		$(menu).css("background-color", settings.parentBGHoverColor);
    	    }

    	    if(settings.parentTextHoverColor){
        		if(!$(menu).attr("dropdownParentOriginalTextColor")){$(menu).attr("dropdownParentOriginalTextColor", $(menu).children("span").children("a").css("color"));}
        		$(menu).children("span").children("a").css("color", settings.parentTextHoverColor);
    	    }

    	    $(menu).children("ul").css("position", "absolute").css("left", $(menu).css("left")).css("z-index", settings.zIndex);

    	    $(menu).children("ul").slideDown(settings.showSpeed, function(){
    		$(this).parent().attr("showing", "0");
    	    });
    	}
    }

    function hide(menu){
	$(menu).children("ul").slideUp(settings.hideSpeed, function(){

	    if(settings.parentBGHoverColor){
		    $(this).parent().css("background-color", $(this).parent().attr("dropdownParentOriginalBGColor"));
	    }

	    if(settings.parentTextHoverColor){
		    $(this).parent().children("span").children("a").css("color", $(this).parent().attr("dropdownParentOriginalTextColor"));
	    }
	});
    }

    return this;
};

$(document).ready(function(){

    $("#header .bottom ul").dropdown({
    	showSpeed: 230,
    	hideSpeed: 230
    });

    $("#mobile-menu .menu a").click(function(){
	       $("#mobile-main-menu").slideToggle(500);
    });

    $(window).resize(function(){
    	if($(window).width() > 480){
    	    $("#mobile-main-menu").hide();
    	}
    });

    $('*').bind('touchstart touchend', function(e) {
    	var that = this;
    	this.onclick = function() {
    	    that.onhover.call(that);
    	};
    });

    $('#pre-header .search form, #pre-header .search form input[type="image"]').click(function(event){
        var text=$('#pre-header .search form input[type="text"]');
        if(text.css('display') == "none"){
            text.css('width', '0px');
            text.css("display", 'inline-block');
            text.animate({width: '200px'}, 350);
            event.preventDefault();
        }

        text.focus();
    });
});
