import moment from 'moment'

import Api from '../api'

// Here are things which we get from 'classic' javascript.
// Eventually we want to eliminate these, but for now this gives a pathway for eslint.
const { $ } = window

/**
 * Find all spans with class date, and set value to localized date string
 * e.g.
 *      en-US: 12/2/15
 *      en-UK: 2/12/15
 */
window.updateDates = function(context) {
    $('span.date', context).each(function (index, dateElem) {
        var formatted = moment($(dateElem).data('date')).format('l');
        formatted = formatted.replace(/\d\d(\d\d)/, "$1");
        $(dateElem).text(formatted);
    });
}

/**
 * Initialize all uninitialized datatables
 * @param options
 * @param tableClass
 * @param context: If specified, must be a DOM context for jquery to work with.
 */
window.initDataTables = function(options, tableClass, context) {
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
        $('table.' + tableClass, context).each(function() {
            var table = $(this);
            table.removeClass(tableClass).dataTable(options);
        })
    });
}

/**
 * Super gross workaround for Chrome not respecting autocomplete settings.
 * Only using this to prevent autofilling the user's name and email into forms that
 * take info about a different person
 */
$(function() {
    $('form[autocomplete="off"] input, input[autocomplete="off"]').each(function () {

        var input = this;
        var name = $(input).attr('name');
        var id = $(input).attr('id');

        $(input).removeAttr('name');
        $(input).removeAttr('id');

        setTimeout(function () {
            $(input).attr('name', name);
            $(input).attr('id', id);
        }, 1);
    });
});

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
        window._beforeSend = function (request) {
            request.setRequestHeader("X-CSRF-TOKEN", config.session.csrfToken);
        }
        var feedbackFormDirty = false;

        function resetFeedbackForm() {
            $("#feedbackSubmitResult").hide();
            $("#feedbackForm").show();

            $("#submitFeedback").attr("disabled", false);
            $("#submitFeedback").show();

            $("#submitFeedbackCancel").val('Cancel');

            // Reset data
            $("#feedbackSubmitResult").find("span.message").html('')
            $("input[name=name]").val(config.user.firstName);
            $("input[name=email]").val(config.user.email);
            $("select[name=topic]").val("");
            $("textarea[name=message]").val("");

            // Clear errors
            resetFeedbackFormErrors()

            $("input[name=copySender]").prop('checked', true);

            feedbackFormDirty = false;
        }

        function resetFeedbackFormErrors() {
            $("#feedback-name").removeClass("has-error")
            $("#feedback-email").removeClass("has-error")
            $("#feedback-topic").removeClass("has-error")
            $("#feedback-message").removeClass("has-error")
        }

        resetFeedbackForm();

        $("#contactLink,#helpFeedback").on('click', function() {

            if (feedbackFormDirty) {
                resetFeedbackForm();
            }

            $('#feedbackModel').modal('show');
        });

        $("#submitFeedback").on('click', function() {

            $("#submitFeedback").attr("disabled", true);

            resetFeedbackFormErrors()

            var data = {};
            data.dataType = 'JSON';
            data.name = $("input[name=name]").val();
            data.email = $("input[name=email]").val();
            data.topic = $("select[name=topic]").val();
            data.message = $("textarea[name=message]").val();
            data.feedbackUrl = location.href;

            var copySender = $("input[name=copySender]").is(":checked");
            if (copySender) {
                data.copySender = copySender;
            }

            feedbackFormDirty = true;

            $.ajax({
                type: "POST",
                url: config.feedbackUrl,
                data: $.param(data),
                beforeSend: window._beforeSend,
                success: function(response) {
                    var $resultDiv = $("#feedbackSubmitResult");
                    $resultDiv.find("span.message").html(response.message);
                    if (response.success) {
                        $resultDiv.removeClass("alert-danger");
                        $resultDiv.addClass("alert-success");

                        $("#feedbackForm").hide();
                        $("#submitFeedback").hide();
                        $("#submitFeedbackCancel").html('Close');
                    } else {
                        $resultDiv.removeClass("alert-success");
                        $resultDiv.addClass("alert-danger");

                        config.session.csrfToken = response.csrf_token

                        $("#feedback-"+response.field).addClass("has-error")

                        $("#feedbackForm").show();
                        $("#submitFeedback").show();
                        $("#submitFeedback").attr("disabled", false);
                    }
                    $resultDiv.show();
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
        const jstz = require('jstz')
        var tz = jstz.determine()
        var locale = navigator.language

        var data = {}
        if (typeof (tz) !== 'undefined') {
            data.timezone = tz.name();
        }
        if (locale) {
            data.locale = navigator.language;
        }

        if (!$.isEmptyObject(data)) {
            Api.UserProfile.setLocale(data).then(function() {
                if (config.isHome){
                    location.reload()
                }
            });
        }
    };

    var Tmlp = {
        enableFeedback: enableFeedback,
        setTimezone: setTimezone,
        Api: Api,
    };
    return Tmlp;
})(window, $);


/**
 * NAVBAR AND DATE SELECT HELPERS
 */
// Enable hover dropdowns in nav menu
$(function () {
    function isBreakpoint( alias ) {
        return $('.device-' + alias).is(':visible');
    }
    if (!isBreakpoint('xs')) {
        $('.dropdown').hover(
            function () {
                $('.dropdown-menu', this).stop(true, true).fadeIn("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            },
            function () {
                $('.dropdown-menu', this).stop(true, true).fadeOut("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            }
        )

    }
    const reportSelect = $('#reportSelect')
    if (reportSelect.hasClass('ajax-report-select')) {
        reportSelect.on('click', 'li.menu-option', function (e) {
            const li = $(this)
            var url = li.data('url')
            var data = {
                date: li.data('value')
            }

            $.ajax({
                type: 'POST',
                url: url,
                beforeSend: window._beforeSend,
                data: $.param(data),
                success: function (response) {
                    if (response.success) {
                        location.reload()
                    }
                }
            })
        })
    }
});
