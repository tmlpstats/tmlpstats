

export function checkStatus(response) {
    if (response.status >= 200 && response.status < 300) {
        return response
    } else {
        // For non-2xx, try to handle it as a JSON response.
        // If it's not JSON, we can use the text instead.
        return response.text().then((text) => {
            var error = new Error(response.statusText)
            error.response = response

            try {
                var jsonVal = JSON.parse(text)
                if (jsonVal.error) {
                    jsonVal = jsonVal.error
                }
                error.error = jsonVal
            } catch (err) {
                // We got a response which was also malformed json.
                error.error = {raw: text}
            }
            throw error
        })
    }
}

export function parseJSON(response) {
    return response.json()
}
