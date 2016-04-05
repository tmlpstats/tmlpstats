/**
 * Get a human readable message from the HTTP status code
 * @param code
 * @returns {string}
 */
function getErrorMessage(code) {
    var message = '';
    if (code == 404) {
        message = 'Unable to find report.';
    } else if (code == 403) {
        message = 'You do not have access to this report.';
    } else {
        message = 'Unable to get report.';
    }
    return message;
}

/**
 * Find all spans with class date, and set value to localized date string
 * e.g.
 *      en-US: 12/2/15
 *      en-UK: 2/12/15
 */
function updateDates() {
    $('span.date').each(function (index, dateElem) {
        var formatted = moment($(dateElem).data('date')).format('l');
        formatted = formatted.replace(/\d\d(\d\d)/, "$1");
        $(dateElem).text(formatted);
    });
}

/**
 * Initialize all uninitialized datatables
 * @param options
 * @param tableClass
 */
function initDataTables(options, tableClass) {
    if (!options) {
        tableClass = 'want-datatable';
        options = {
            "paging":    false,
            "searching": false
        };
    }
    // document.ready may fire immediately depending on the current state of the DOM
    // We use the want-datatable class as a flip to avoid continuously binding tables
    $(document).ready(function() {
        $('table.' + tableClass).each(function() {
            var table = $(this);
            table.removeClass(tableClass).dataTable(options);
        })
    });
}

/**
 * JS API for TMLP things.
 *
 * This API is designed to be optimized by javascript optimizing compilers
 * (like closure compiler and so on) hence keeping local closure references
 * to window and jquery.
 */
window.Tmlp = (function(window, $) {
    ////////////////////////////
    ///////// FEEDBACK SYSTEM
    function enableFeedback(config) {
        var feedbackFormDirty = false;

        function resetFeedbackForm() {

            $("#feedbackSubmitResult").hide();
            $("#feedbackForm").show();

            $("#submitFeedback").attr("disabled", false);
            $("#submitFeedback").show();

            $("#submitFeedbackCancel").val('Cancel');


            $("input[name=name]").val(config.firstName);
            $("input[name=email]").val(config.email);
            $("textarea[name=message]").val("");
            $("input[name=copySender]").prop('checked', true);

            feedbackFormDirty = false;
        }

        resetFeedbackForm();

            $("#contactLink").on('click', function() {

                if (feedbackFormDirty) {
                    resetFeedbackForm();
                }

                $('#feedbackModel').modal('show');
            });

            $("#submitFeedback").on('click', function() {

                $("#submitFeedback").attr("disabled", true);

                var data = {};
                data.dataType = 'JSON';
                data.name = $("input[name=name]").val();
                data.email = $("input[name=email]").val();
                data.message = $("textarea[name=message]").val();

                var copySender = $("input[name=copySender]").val();
                if (copySender) {
                    data.copySender = copySender;
                }

                feedbackFormDirty = true;

                $.ajax({
                    type: "POST",
                    url: config.feedbackUrl,
                    data: $.param(data),
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", config.csrfToken);
                    },
                    success: function(response) {
                        var $resultDiv = $("#feedbackSubmitResult");
                        $resultDiv.find("span.message").html(response.message);
                        if (response.success) {
                            $resultDiv.removeClass("alert-danger");
                            $resultDiv.addClass("alert-success");
                        } else {
                            $resultDiv.removeClass("alert-success");
                            $resultDiv.addClass("alert-danger");
                        }
                        $resultDiv.show();

                        $("#feedbackForm").hide();
                        $("#submitFeedback").hide();
                        $("#submitFeedbackCancel").html('Close');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var code = jqXHR.status;

                        var message = '';
                        if (code == 404) {
                            message = 'We were unable to find that report. Please try validating and submitting your report again.';
                        } else if (code == 403) {
                            message = 'You are not allowed to submit this report.';
                        } else {
                            message = 'There was a problem submitting your report. Please try again.';
                        }

                        var $resultDiv = $("#feedbackSubmitResult");
                        $resultDiv.find("span.message").html('<p>' + message + '</p>');
                        $resultDiv.removeClass("alert-success");
                        $resultDiv.addClass("alert-danger");
                        $resultDiv.show();

                        $("#submitFeedback").attr("disabled", false);
                    }
                });
            });
    }

    //////////////////////////////
    //////////// TIMEZONE SETTING
    var setTimezone = function(config) {
        var tz = jstz.determine();
        var locale = navigator.language;

        var data = {};
        if (typeof (tz) !== 'undefined') {
            data.timezone = tz.name();
        }
        if (locale) {
            data.locale = navigator.language;
        }

        if (!$.isEmptyObject(data)) {
            Api.UserProfile.setLocale(data, function() {
                if (config.isHome){
                    location.reload();
                }
            });
        }
    };

    var Tmlp = {
        enableFeedback: enableFeedback,
        setTimezone: setTimezone
    };
    return Tmlp;
})(window, jQuery);
