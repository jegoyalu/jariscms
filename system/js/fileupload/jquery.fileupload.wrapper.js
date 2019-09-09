/*
 * Wrapper around jquery.fileupload plugin to adjust the functionality
 * for jaris cms.
 *
 * Wrapper written by: Jefferson González
 */
$.fn.fileuploadwrapper = function(options) {
    var defaults = {
        showDescriptionField: false,
        acceptFileTypes: "",
        singleUpload: false,
        incorrectFileTypeMessage: "Incorrect file type selected. The type should be:",
        maxFiles: 0,
        maxFilesMessage: "Maximum amount of files allowed reached.",
        currentFiles: "",
        maxSize: 0,
        maxFileSizeMessage: "The file exceeds the maximum allowed.",
        descriptionPlaceholder: "description"
    };

    var settings = $.extend({}, defaults, options);

    var nextFileID = 0;

    var fileCount = 0;

    var id = $(this).attr("id");

    var fileInputName = $(this).attr("name").replace(/(\[|\])/g, "").trim();

    this.each(function() {
        $(this).after(
            '<table id="'+id+'-file-upload-list" class="file-upload-list">' +
                '<tbody></tbody>' +
            '</table>'
        );

        $(this).fileupload({
            dataType: 'json',

            paramName: 'files[]',

            add: function(e, data){

                if(settings.singleUpload == true && fileCount > 0) {
                    return;
                }
                else if(settings.singleUpload == false && settings.maxFiles > 0) {
                    if(fileCount >= settings.maxFiles) {
                        alert(settings.maxFilesMessage + " ("+settings.maxFiles+")");
                        return;
                    }

                    if(settings.currentFiles != "") {
                        if(($(settings.currentFiles).length + fileCount) >= settings.maxFiles) {
                            alert(settings.maxFilesMessage + " ("+settings.maxFiles+")");
                            return;
                        }
                    }
                }

                var file = data.files[0];

                if(settings.maxSize > 0) {
                    if(file.size > settings.maxSize) {
                        if((settings.maxSize / 1024 / 1024) < 1) {
                            alert(settings.maxFileSizeMessage + " ("+settings.maxSize/1024+"KB)");
                        }
                        else {
                            alert(settings.maxFileSizeMessage + " ("+settings.maxSize/1024/1024+"MB)");
                        }
                        return;
                    }
                }

                if(settings.acceptFileTypes.length > 0) {
                    var extensions = settings.acceptFileTypes.split(",");
                    var file_extension = file.name.split(".");
                    var found_extension = false;
                    file_extension = file_extension[file_extension.length-1];

                    for(var index in extensions) {
                        var extension = $.trim(extensions[index].toLowerCase());
                        if(extension == file_extension.toLowerCase()) {
                            found_extension = true;
                            break;
                        }
                    }
                    if(!found_extension) {
                        alert(settings.incorrectFileTypeMessage + " " + settings.acceptFileTypes.replace(/\,/g, ", "));
                        return;
                    }
                }

                file.id = nextFileID;
                $('#'+id+'-file-upload-list tbody').append(
                    '<tr class="file-'+nextFileID+'" style="display:none">' +
                        '<td class="name">'+file.name+'</td>' +
                        '<td class="percent"></td>' +
                        '<td class="progress"><div class="container"><div class="bar" style="width: 0%;"></div></div></td>' +
                        '<td class="delete"><a class="cancel">x</a></td>' +
                    '</tr>'
                );

                if(settings.showDescriptionField) {
                    $('#'+id+'-file-upload-list tbody .file-'+nextFileID+' .name').after(
                        '<td class="description"><input type="text" placeholder="'+settings.descriptionPlaceholder+'" name="'+fileInputName+'[descriptions][]" /></td>'
                    );
                }

                $('#'+id+'-file-upload-list .file-'+nextFileID).fadeIn(1000);

                data.context = $('#'+id+'-file-upload-list .file-'+nextFileID+' .bar');

                var jqXHR = data.submit();
                $('#'+id+'-file-upload-list tbody .file-'+nextFileID+' .cancel').click(function(){
                    jqXHR.abort();
                    $(this).parent().parent().fadeOut(1000, function(){
                        $(this).remove();
                        fileCount--;
                    });
                }).css('cursor', 'pointer');

                nextFileID++;
                fileCount++;
            },

            progress: function (e, data) {
                if(data.context) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    data.context.parent().parent().prev().text(progress+'%');
                    data.context.css('width', progress+'%');
                }
            },

            fail: function(e, data) {
                alert("Error: " + data.textStatus + " (" + data.errorThrown + ")");
            },

            done: function (e, data) {
                if(data.context) {
                    var file = data.files[0];

                    if(data.result[0].error) {
                        alert(data.result[0].error);
                        $('#'+id+'-file-upload-list tbody .file-'+file.id).fadeOut(1000, function(){$(this).remove();});
                        return;
                    }

                    data.context.css('width', '100%');
                    $('#'+id+'-file-upload-list tbody .file-'+file.id+' .name').html(
                        data.result[0].name +
                        '<input type="hidden" name="'+fileInputName+'[names][]" value="'+data.result[0].name+'" />' +
                        '<input type="hidden" name="'+fileInputName+'[types][]" value="'+data.result[0].type+'" />'
                    );
                    data.context.parent().parent().prev().text('100%');
                    data.context.parent().parent().next().html('<a class="delete-file-'+file.id+'">x</a>');

                    $('#'+id+'-file-upload-list tbody .delete-file-'+file.id).click(function(){
                        var that = this;
                        $.ajax({
                            url:data.result[0].delete_url,
                            type:"POST",
                            dataType:"json",
                            success: function(data, textStatus, jqXHR){
                                $(that).parent().parent().fadeOut(1000, function(){$(this).remove();});
                                fileCount--;
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert(textStatus + ": " + errorThrown);
                            }
                        });
                    }).css('cursor', 'pointer');
                }
                else {
                    $.each(data.files, function(index, files){
                        alert(files.name);
                    });
                }
            }
        })
        .error(function (jqXHR, textStatus, errorThrown){
            alert(textStatus + ": " + errorThrown);
        });
    });

    return this;
};