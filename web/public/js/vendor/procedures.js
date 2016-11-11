function startProcedure() {
    $('#validate-info-employer').on("click", function () {
        $('#employer-info-content').toggle();
    });
    $('#validate-doc-employer').on("click", function () {
        $('#employer-info-content').toggle();
    });
    $('#validate-rut-employer').on("click", function () {
        $('#employer-docs-content').toggle();
    });
    $('#validate-mand-employer').on("click", function () {
        $('#employer-docs-content').toggle();
    });
    $('#aditional-info').on("click", function () {
        $('#aditional-info-content').toggle();
    });
    var employeesCount = $("input[id='employees_count']").val();
    var employerEntitiesCount = $("input[id='employer_entities_count']").val();
    var employerWorkplacesCount = $("input[id='employer_workplace_count']").val();
    for (var  i = 1; i <= employerEntitiesCount; i++) {
        var count = i+'';
        $('#validate-entity-employer'+(count+'')).on("click", function () {
            $('#employer-entities-content').toggle();
        });
        $('#form_employer_entity_'+ count).on("click",function () {
            var val = ($(this).attr('id')+'').split('_')[3];
            $("input",this).on("change",function () {
                $("#validate_employer_entity_"+val+"_btn").hide();
            });
            $("select",this).on("change",function () {
                $("#validate_employer_entity_"+val+"_btn").hide();
            });
        });
    }
    for (var j = 0; j < employerWorkplacesCount; j++) {
        var count = j+'';
        $('#workplace-button_' + (count+'')).on("click", function () {
            $('#workplace-info' + ($(this).attr('id')+'').split('_')[1]).toggle();
            $('#workplace-form' + ($(this).attr('id')+'').split('_')[1]).toggle();

        });
        $('#workplaceCancel_' + (count+'')).on("click", function () {
            $('#workplace-info' + ($(this).attr('id')+'').split('_')[1]).toggle();
            $('#workplace-form' + ($(this).attr('id')+'').split('_')[1]).toggle();
        });
    }
    for (var i = 1; i <= employeesCount; i++) {
        $('#eployee_' + i).on("click", function () {
            $('#employee-content' + ($(this).attr('id')+'').split('_')[1]).toggle();
        });
        $('#validate-info-employee_'+i).on("click",function () {
            $('#employee-info-content'+ ($(this).attr('id')+'').split('_')[1]).toggle();
        });
        $('#validate-doc-employee_'+i).on("click",function () {
            $('#employee-info-content'+($(this).attr('id')+'').split('_')[1]).toggle();
        });
        $('#validate-cat-employee_'+i).on("click",function () {
            $('#employee-info-content'+($(this).attr('id')+'').split('_')[1]).toggle();
        });
        $('#aditional-actions_'+i).on("click",function () {
            $('#aditional-actions-content'+($(this).attr('id')+'').split('_')[1]).toggle();
        });
        var value ='employee_entities_count_'+i;
        var employeeEntitiesCount = $("#"+value).val();
        for(var j = 1 ; j<=employeeEntitiesCount;j++){
            $('#validate-entity-employee_'+i+'_'+j).on("click",function () {
                $('#employee-entities-content'+($(this).attr('id')+'').split('_')[1]).toggle();
            });
            $("#entity_employee_"+i+"_"+j).on("click",function () {
                var val = ($(this).attr('id')+'').split('_')[2];
                var btn = ($(this).attr('id')+'').split('_')[3];
                $("input",this).on("change",function () {
                    $("#employee_entity_"+val+"_"+btn).hide();
                });
                $("select",this).on("change",function () {
                    $("#employee_entity_"+val+"_"+btn).hide();
                });
            });
        }
        $("#formInfoEmployee_"+i).on("click",function () {
            var val = ($(this).attr('id')+'').split('_')[1];
            $("input",this).on("change keyup paste",function () {
                $("#validate_employee_info_"+val).hide();
                $("#save_info_employee_"+val).show();
            });
            $("select",this).on("change",function () {
                $("#validate_employee_info_"+val).hide();
                $("#save_info_employee_"+val).show();
            });
        });
    }

    $(".download-link").on('click', function (event) {
        event.preventDefault();
        var uri = $(this).attr("href");
        var html = '';
//                 alert(uri);
        $.ajax({
            //method: "POST",
            url: uri,
            //(data: $('form[name="pago_membresia"]').serialize(),
            beforeSend: function (xhr) {
            }
        }).done(function (data) {
            html = data;
//                    alert(html);
            $("#modal-body").html('');

            $('#modal-body').html($(html).find('#cuerpoModal'));
            $("#myModal").modal();
            $("#myModal").modal('show');

            var files;
            // Add events
            $('input[type=file]').on('change', prepareUpload);

            // Grab the files and set them to our variable
            function prepareUpload(event) {
                files = event.target.files;
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);

        });
    });

    $("form[id='form_employer_info']").on("click",function () {
        $("input",this).on("change keyup paste",function () {
            $("#validate_info_employer_btn").hide();
            $("#validate_info_employer_save").show();
        });
        $("select",this).on("change",function () {
            $("#validate_info_employer_btn").hide();
            $("#validate_info_employer_save").show();
        });
    });

}