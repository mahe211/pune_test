/* 
 * This basically used on Validation of imported data, validate valid, invalid, duplicate data 
 *  
 */

var request;
$('#validatecsv').on('click', function (event) {

    event.preventDefault();

    if (request) {
        request.abort();
    }
    var $form = $('#validate_save_csv');

    var $inputs = $form.find("input");

    var serializedData = $form.serializeArray();

    $inputs.prop("disabled", true);

    request = $.ajax({
        url: "validate-js-data",
        type: "post",
        data: serializedData,
    })
            .done(function (response, textStatus, jqXHR) {
                var counter = 0;
                $.each(response, function (k, v) {
                    var i = counter;
                    if (($('#sap_id_' + i).length) == 0) {
                        i = ++counter;
                    }

                    $('#sap_id_' + i).val(v.sap_id);
                    $('#hostname_' + i).val(v.hostname);
                    $('#loopback_' + i).val(v.loopback);
                    $('#mac_address_' + i).val(v.mac_address);

                    $('#highlight_duplicate_' + i).prop('class', v.duplicate);
                    $('#SAPHighlight_' + i).prop('class', v.sap_id_error_class);
                    $('#HostnameHighlight_' + i).prop('class', v.hostname_error_class);
                    $('#LoopbackHighlight_' + i).prop('class', v.loopback_error_class);
                    $('#MACHighlight_' + i).prop('class', v.mac_address_error_class);
                    $('#error_input_' + i).val('0');
                    if (v.duplicate || v.sap_id_error_class || v.hostname_error_class || v.loopback_error_class || v.mac_address_error_class) {
                        $('#error_input_' + i).val('1');
                    }
                    counter++;
                });
                var values = $("input[name='error_input[]']")
                        .map(function () {
                            return $(this).val();
                        }).get();
                $('#confirm_continue').prop('disabled', false);
                if (jQuery.inArray("1", values) !== -1) {
                    $('#confirm_continue').prop('disabled', true);
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert('Failed to validate data');
                console.error(
                        "The following error occurred: " +
                        textStatus, errorThrown
                        );
            });
    request.always(function () {
        $inputs.prop("disabled", false);
    });
});


$(document).ready(function () {
    // Delete row on delete button click
    $('.delete').on("click", function () {
        $(this).parents("tr").remove();
    });

    var values = $("input[name='error_input[]']")
            .map(function () {
                return $(this).val();
            }).get();
    $('#confirm_continue').prop('disabled', false);
    if (jQuery.inArray("1", values) !== -1) {
        $('#confirm_continue').prop('disabled', true);
    }

});


function verifyAndConfirm(e)
{
    if (!confirm('We only proceed valid records, invalid records will not save, are you sure to continue?')) {
        e.preventDefault();
    }
}