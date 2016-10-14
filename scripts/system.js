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

$(document).ready(function() {
    /* Start collapsing */
    start_collapse();

    /* jQuery textarea resizer plugin usage */
    $('textarea.form-textarea:not(.processed)').TextAreaResizer();
});