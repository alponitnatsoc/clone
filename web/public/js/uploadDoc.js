
/**
 * Created by Andres on 09/06/16.
 */
function startUpload() {

    var filesCount = 0 ;
    $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/jquery.validate.min.js").done(function () {
        $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/additional-methods.min.js").done(function () {
            $("form[name='add_multiple_files']").validate({
                submitHandler: function(form) {
                    form.submit();}
            });

            jQuery.validator.addMethod("filesize", function(value, element) {
                if (element.files[0].size>1024000){
                    return false;
                }
                return true;
            }, "Max file size allowed is 1Mb");
        });
    });

    $("#show_add_single_file").click(function(){
        $("#add_document_content").hide();
        $("#upload_multiple_doc_form").hide();
        $("#upload_single_doc_form").show();
    });
    $("#show_add_multiple_file").click(function(){
        if(filesCount==0){
            $("#add_file_link").trigger("click");
        }
        $("#add_document_content").hide();
        $("#upload_single_doc_form").hide();
        $("#upload_multiple_doc_form").show();
    });

    $("#add_doc_cancel_button1").click(function(){
        $("#upload_single_doc_form").hide();
        $("#upload_multiple_doc_form").hide();
        $("#add_document_content").show();
    });

    $("#add_doc_cancel_button2").click(function(){
        while(filesCount>1){
            $('#remove_file_link').trigger("click");
        }
        $("#upload_single_doc_form").hide();
        $("#upload_multiple_doc_form").hide();
        $("#add_document_content").show();
    });


    $('#add_file_link').on('click', function (e){
        e.preventDefault();
        var fileList = $("#file-field-list");
        var newWidget = fileList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g,filesCount);
        var newLi = $('<li class="col-md-12 files" style="list-style-type: none; padding: 0"></li>').html(newWidget);
        filesCount++;
        newLi.appendTo(fileList);

        $("#file-field-list").find("li").find("input[name*=image]").not("[type='hidden']").last().rules( "add", {
            required: true,
            accept: "image/jpeg,image/png,image/bmp",
            filesize: true,

            messages: {
                required: "Por favor selecciona una imagen",
                accept: "La imagen no tiene un formato válido, los formatos permitidos son: .jpg .jpeg .png .bmp.",
                filesize: "La imagen debe pesar menos de 1Mb"
            }
        });
    });

    $('#add_multiple_files_save').on('click',function (e) {
        $("#add_multiple_files_save").trigger("click");
    });


    $('#remove_file_link').on('click',function (e) {
        if(filesCount>1){
            $("#file-field-list").find("li[class*=files]").last().remove();
            filesCount--;
        }

    });


}
