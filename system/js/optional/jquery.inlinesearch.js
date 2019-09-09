/*
Example:
$("table").inlineSearch();
*/
(function($){
     $.fn.extend({
         inlineSearch: function(options, element) {
            try{
                var defaults = {
                    searchSelector: "tbody tr",
                    placeholder: "search...",
                    remember: true
                };

                var settings = $.extend({}, defaults, options);
                var self = $(this);
                var search_box = $(
                    '<div class="inline-search">'
                        + '<input type="text" '
                        + 'autofocus '
                        + 'placeholder="'+settings.placeholder+'"'
                        + ' />'
                        + '</div>'
                );

                var element_ls = "";

                if(typeof(localStorage) !== undefined && settings.remember){
                    var path = window.location.pathname;
                    element_ls = path
                        + self.attr("id")
                        + self.attr("class")
                        + self.get(0).nodeName
                    ;

                    $(search_box).children("input")
                        .val(localStorage.getItem(element_ls))
                    ;
                }

                $(search_box).children("input").on("keyup", function() {
                    var value = $(this).val().toLowerCase();

                    if(typeof(localStorage) !== undefined && settings.remember){
                        if(value == ""){
                            localStorage.removeItem(element_ls);
                        } else{
                            localStorage.setItem(
                                element_ls,
                                $(this).val()
                            );
                        }
                    }

                    self.find(settings.searchSelector).each(function(index) {
                        var row = $(this);

                        var id = row.text().toLowerCase();

                        var position = id.indexOf(value);

                        if(position === -1){
                            row.hide();
                        }
                        else{
                            row.show();
                        }
                    });
                }).trigger("keyup");

                $(search_box).insertBefore(self);

                $(search_box).append(
                    '<style type="text/css">'
                    + '.inline-search {text-align: right; border-bottom: solid 1px #d3d3d3; padding: 10px 0 5px 0; margin-bottom: 5px;} '
                    + '.inline-search input {width: 300px;} '
                    + '@media screen and (max-width: 650px){ '
                    + '.inline-search {text-align: center;} '
                    + '.inline-search input {width: 95%; margin: 0 auto 0 auto} '
                    + '}'
                    + '</style>'
                );
            }
            catch(error){}
        }
    });
})(jQuery);
