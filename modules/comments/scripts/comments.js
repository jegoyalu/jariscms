$.commentsModule = function(options) {
    var defaults = {
        add_comment_url: "",
        flag_comment_url: "",
        delete_comment_url: "",
        navigation_url: "",
        page_uri: "",
        page_type: "",
        cascade: true,
        maximum_characters: 150,
        translations: []
    };

    var settings = $.extend({}, defaults, options);

    var parent = this;

    var sending_reply = false;

    parent.comments_page = function(page)
    {
        $.post(
            settings.navigation_url,
            {
                uri: settings.page_uri,
                page: page,
                type: settings.page_type
            },
            parent.comments_content
        );
    };

    parent.comments_content = function(data)
    {
        $("#comments").html(data);

        $(".comment-flag-link").click(parent.flag_submit);

        $(".comment-reply-link").click(parent.reply_show);

        $(".comment-delete-link").click(parent.delete_submit);

        $("#comments-navigation a[data-page]").click(function(){
            parent.comments_page($(this).attr("data-page"));
        });
    };

    parent.comment_submit = function()
    {
        if ($.trim($("#add-comment-comment").val()) != "")
        {
            $.post(
                settings.add_comment_url,
                {
                    comment: $("#add-comment-comment").val(),
                    page: settings.page_uri,
                    type: settings.page_type
                },
                parent.add_comment
            );

            $(this).attr("disabled", true);
        }
    };

    parent.reply_cancel = function()
    {
        if ($(".reply-input") != undefined)
        {
            $(".reply-input").remove();
        }

        sending_reply = false;
    };

    parent.reply_show = function()
    {
        parent.reply_cancel();

        var id_elements = $(this).attr(("id")).split("-");
        var id = id_elements[2];
        var user = id_elements[3];

        var id_submit = "reply-" + id + "-" + user;

        var content_html = '<div class="reply-input">';
        content_html += '<textarea id="reply-comment-text" class="reply-textarea"></textarea>';
        content_html += '<input type="button" id="' + id_submit + '" class="reply-comment-submit" value="'+t("reply")+'" />';
        content_html += ' <input type="button" class="reply-comment-cancel" value="'+t("cancel")+'" />';
        content_html += ' <span  id="reply-chars-left"></span>&nbsp;'+t("characters left")+'.';
        content_html += '</div>';

        $(this).parent().parent().children(".comment-content").prepend($(content_html).hide().fadeIn());

        $(".reply-comment-submit").click(parent.reply_submit);
        $(".reply-comment-cancel").click(parent.reply_cancel);
        $("#reply-comment-text").limit(settings.maximum_characters, '#reply-chars-left');
        $("#reply-comment-text").TextAreaResizer();
    };

    parent.reply_submit = function()
    {
        sending_reply = true;

        if ($.trim($("#reply-comment-text").val()) != "")
        {
            var id_elements = $(this).attr(("id")).split("-");
            var id = id_elements[1];
            var user = id_elements[2];

            $.post(
                settings.add_comment_url,
                {
                    comment: $("#reply-comment-text").val(),
                    page: settings.page_uri,
                    type: settings.page_type,
                    rid: id
                },
                parent.add_comment
            );

            $(this).attr("disabled", true);
            $(".reply-comment-cancel").attr("disabled", true);
        }
    };

    parent.flag_submit = function()
    {
        var id_elements = $(this).attr(("id")).split("-");
        var id = id_elements[2];
        var user = id_elements[3];

        $.post(
            settings.flag_comment_url,
            {
                id: id,
                page: settings.page_uri,
                type: settings.page_type,
                user: user
            },
            parent.flag_comment
        );

        $(this).unbind("click");
    };

    parent.delete_submit = function()
    {
        var id_elements = $(this).attr(("id")).split("-");
        var id = id_elements[2];
        var user = id_elements[3];

        $.post(
            settings.delete_comment_url,
            {
                id: id,
                page: settings.page_uri,
                type: settings.page_type,
                user: user
            },
            parent.delete_comment
        );

        $(this).unbind("click");
    };

    $(document).ready(function() {

        $("#add-comment-submit").click(parent.comment_submit);

        $("#add-comment-reset").click(function() {
            $("#add-comment-comment").val("");
        });

        $("#add-comment-comment").limit(settings.maximum_characters, '#add-comment-left');

        parent.comments_page(1);
    });

    parent.add_comment = function(data)
    {
        if ($.trim(data) != "")
        {
            if(settings.cascade && sending_reply){
                $(".reply-input").parent().parent()
                    .children(".comment-replies")
                    .prepend($(data).hide().fadeIn())
                ;
            }
            else{
                $("#comments").prepend($(data).hide().fadeIn());
            }

            parent.reply_cancel();

            $("#add-comment-comment").val("");
            $("#add-comment-submit").attr("disabled", false);

            $(".comment-flag-link").unbind("click");
            $(".comment-delete-link").unbind("click");

            $(".comment-flag-link").click(parent.flag_submit);
            $(".comment-reply-link").click(parent.reply_show);
            $(".comment-delete-link").click(parent.delete_submit);
        }
    };

    parent.flag_comment = function(data)
    {
        $("#comment-" + data.toString()).children(".comment-actions").children(".comment-flag-link").fadeOut();
    };

    parent.delete_comment = function(data)
    {
        $("#comment-" + data.toString()).fadeOut();
    };

    function t(text)
    {
        if(settings.translations[text]){
            return settings.translations[text];
        }

        return text;
    }

    return this;
};