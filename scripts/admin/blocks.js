function reposition_block_edit_button()
{
    $(".instant-block-edit, .instant-content-block-edit").each(function(index){
        var position = $(this).parent().position();

        $(this).
        css("left", parseInt(position.left+$(this).parent().innerWidth()-$(this).innerWidth())).
        css("top", parseInt(position.top));

        $(this).parent().children(".instant-block-edit-container").
        css("width", parseInt($(this).parent().innerWidth())).
        css("height", parseInt($(this).parent().innerHeight())).
        css("left", parseInt(position.left)).
        css("top", parseInt(position.top));
    });
}

$(document).ready(function() {
    /* Reposition block edit buttons */
    $(".instant-block-edit, .instant-content-block-edit").each(function(index){

        var position = $(this).parent().position();

        var container = $('<div class="instant-block-edit-container"></div>');

        var parent = $(this).parent();

        $(this).css("opacity", "0.3").
        css("z-index", "30000").
        css("position", "absolute").
        css("left", parseInt(position.left+$(this).parent().innerWidth()-$(this).innerWidth())).
        css("top", parseInt(position.top));

        container.css("opacity", "0").
        css("position", "absolute").
        css("border", "dashed 1px " + $(this).css("background-color")).
        css("width", parseInt($(this).parent().innerWidth())).
        css("height", parseInt($(this).parent().innerHeight())).
        css("left", parseInt(position.left)).
        css("top", parseInt(position.top));

        parent.children("*").css("z-index", "1000");

        $($(this)).after(container);

        editBlockInitialPositionTimer = setInterval(
            function(){
                reposition_block_edit_button();
                clearInterval(editBlockInitialPositionTimer);
            },
            100
        );

    }).parent().hover(
        function(){
            $(this).children("*").css({position: "relative"});
            $(this).children(".instant-block-edit, .instant-content-block-edit, .instant-block-edit-container").
            css("position", "absolute").
            animate({opacity: 1}, 300);

            $(this).children(".instant-block-edit, .instant-content-block-edit").css({zIndex: 30000});
        },
        function(){
            $(this).children("*").css({position: "static"});

            $(this).children(".instant-block-edit, .instant-content-block-edit").
            css("position", "absolute").
            animate({opacity: 0.3}, 300);

            $(this).children(".instant-block-edit-container").
            css("position", "absolute").
            animate({opacity: 0}, 300);
        }
    );

    $(window).resize(function(){
        reposition_block_edit_button();
    });

    if("ontouchstart" in document.documentElement){
        $(window).bind("touchstart", function(){
            reposition_block_edit_button();
        });

        $(window).scroll(function(){
            reposition_block_edit_button();
        });
    }
});
