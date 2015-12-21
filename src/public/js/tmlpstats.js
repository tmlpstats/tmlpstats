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
