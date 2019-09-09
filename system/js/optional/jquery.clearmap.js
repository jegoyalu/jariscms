(function ($) {
    $.clearMap = function (id) {
        $("#"+id+'-addr').val("North Pacific Ocean");
        $("#"+id).geolocate('callGeocoding');
        setTimeout(function(){
            $("#"+id+'-lat').val("");
            $("#"+id+'-lng').val("");
            $("#"+id+'-addr').val("");
        }, 3000);
    };
})(jQuery);