jQuery.bibliaNavigation = function(options) {
    var defaults = {
        api_url: "",
        form_name: "",
        biblia: ""
    };

    var settings = $.extend({}, defaults, options);

    var libros_id = "#" + settings.form_name + "-" + "libro";
    var capitulos_id = "#" + settings.form_name + "-" + "capitulo";
    var versiculos_id = "#" + settings.form_name + "-" + "versiculo";

    var self = {};

    self.getCapitulos = function(libro){
        $.get(
            settings.api_url,
            {
                libro: libro,
                biblia: settings.biblia
            },
            null,
            "json"
        ).done(function( data ) {
            if(data.error){
                alert(data.error);
                return;
            }

            $(capitulos_id).html("");
            $(versiculos_id).html("");

            if(Object.keys(data).length > 0) {
                var capitulo = "";
                for(var property in data) {
                    if(capitulo == "") {
                        capitulo = data[property];
                    }
                    $(capitulos_id).append(
                        "<option value=\""+data[property]+"\">"+data[property]+"</option>"
                    );
                }
                self.getVersiculos(libro, capitulo);
            }
        }).fail(function(data){
            alert("Check your internet connection: error encountered when trying to retrieve a list of chapters.");
        });
    }

    self.getVersiculos = function(libro, capitulo){
        $.get(
            settings.api_url,
            {
                libro: libro,
                capitulo: capitulo,
                biblia: settings.biblia
            },
            null,
            "json"
        ).done(function( data ) {
            if(data.error){
                alert(data.error);
                return;
            }

            $(versiculos_id).html("");

            $(versiculos_id).append(
                "<option value=\"\">Todo</option>"
            );

            if(Object.keys(data).length > 0) {
                for(var property in data) {
                    $(versiculos_id).append(
                        "<option value=\""+data[property]+"\">"+data[property]+"</option>"
                    );
                }
            }
        }).fail(function(data){
            alert("Check your internet connection: error encountered when trying to retrieve a list of verses.");
        });
    }

    $(libros_id).change(function(){
        var libro = $(this).val();
        if(libro != "") {
            self.getCapitulos(libro);
        }
    });

    $(capitulos_id).change(function(){
        var libro = $(libros_id).val();
        var capitulo = $(this).val();

        if(libro != "" && capitulo != "") {
            self.getVersiculos(libro, capitulo);
        }
    });
};
