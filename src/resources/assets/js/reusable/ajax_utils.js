// Get a list of messages whether there's one or more.
export function getMessages(input) {
    // If it has a 'messages' key, assume it's an array of message objects.
    if (input.messages) {
        if (process.env.NODE_ENV != 'production') {
            input.messages.forEach((msg, i) => {
                if (!looksLikeMessage(msg)) {
                    console.log(`Message ${i} does not look like a message`, msg)
                }
            })
        }
        return input.messages
    }

    // If it has an 'error' key, check and see if it's a
    if (input.error) {
        input = input.error
    }
    if (looksLikeMessage(input)) {
        return [input]
    } else if (process.env.NODE_ENV != 'production') {
        console.log('warning: got value which doesn\'t resemble an error', input)
    }
    return []
}

function looksLikeMessage(v) {
    return (v && v.level && v.message)
}

// Do the best job of getting 'some' text from an error value. Mostly for early debugging
export function getErrMessage(err) {
    if (err.error) {
        err = err.error
    }
    if (err.message) {
        err = err.message
    }
    return err
}

// Holdover from jquery-based API.
// TODO remove soon
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
