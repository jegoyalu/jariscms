//Starts collapsing all fieldsets
function start_collapse()
{
    $("fieldset.collapsible").each(function(index, element){
        var div = $("<div></div>");
        $(element).children("script").detach();
        $(element).children().detach().appendTo(div);
        $(div).appendTo(element);

        $(element).children("div").children("legend").detach().insertBefore($(element).children("div"));
        if($(element).hasClass("collapsed")){
            $(element).children("div").slideUp(0);
        }
    });

    $("fieldset.collapsible legend").click(function(){
        //Show Content
        if($(this).parent().hasClass("collapsed"))
        {
            $(this).parent().removeClass("collapsed").children("div").slideDown("slow");
            $(this).children("a").removeClass("expand");
            $(this).children("a").addClass("collapse");
        }
        //Hide Content
        else
        {
            $(this).parent().children("div").slideUp("slow", function(){
                $(this).parent().addClass("collapsed");
                $(this).parent().find("legend a").removeClass("collapse");
                $(this).parent().find("legend a").addClass("expand");
            });
        }
    });
}

//Jquery browser for plugins that still use it.
(function() {

    var matched, browser;

    // More details: http://api.jquery.com/jQuery.browser
    jQuery.uaMatch = function( ua ) {
        ua = ua.toLowerCase();

        var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
            /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
            /(msie) ([\w.]+)/.exec( ua ) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
            [];

        return {
            browser: match[ 1 ] || "",
            version: match[ 2 ] || "0"
        };
    };

    matched = jQuery.uaMatch( navigator.userAgent );
    browser = {};

    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }

    jQuery.browser = browser;

    jQuery.sub = function() {
        function jQuerySub( selector, context ) {
            return new jQuerySub.fn.init( selector, context );
        }
        jQuery.extend( true, jQuerySub, this );
        jQuerySub.superclass = this;
        jQuerySub.fn = jQuerySub.prototype = this();
        jQuerySub.fn.constructor = jQuerySub;
        jQuerySub.sub = this.sub;
        jQuerySub.fn.init = function init( selector, context ) {
            if ( context && context instanceof jQuery && !(context instanceof jQuerySub) ) {
                context = jQuerySub( context );
            }

            return jQuery.fn.init.call( this, selector, context, rootjQuerySub );
        };
        jQuerySub.fn.init.prototype = jQuerySub.fn;
        var rootjQuerySub = jQuerySub(document);
        return jQuerySub;
    };

})();

$(document).ready(function() {
    /* Start collapsing */
    start_collapse();

    /* jQuery textarea resizer plugin usage */
    $('textarea.form-textarea:not(.processed)').TextAreaResizer();
});