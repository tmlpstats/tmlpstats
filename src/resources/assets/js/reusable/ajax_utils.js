

export function bestErrorValue(jqXHR, textStatus) {
    var data
    if (jqXHR.responseText) {
        data = jqXHR.responseText
        if (textStatus != 'parsererror' && data.substring(0, 1) == '{') {
            data = JSON.parse(data)
            if (data.error) {
                data = data.error
            }
            if (data.message) {
                data = data.message
            }
        }
    } else {
        data = textStatus
    }
    return data
}
