
import { fetch } from '../reusable/ponyfill'
import { checkStatus, parseJSON } from './http-support'

function jsonApiCall(methodName, params) {
    return fetch(`/api/${methodName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'aplication/json',
            'Accept': 'aplication/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(params)
    }).then(checkStatus).then(parseJSON)
}

export function buildApiCaller(methodName) {
    function apiCall(params) {
        return jsonApiCall(methodName, params)
    }

    // All of this code should get compiled out when we are build in production mode
    if (process.env.NODE_ENV != 'production') {
        mocks._known[methodName] = apiCall

        return function(params) {
            if (mocks[methodName]) {
                // Support mocks for this method name
                return mocks[methodName](params, apiCall)
            } else if (mocks._all) {
                return mocks._all(methodName, params, apiCall)
            } else {
                return apiCall(params)
            }
        }
    } else {
        return apiCall
    }
}


// We use this to allow unit test code to inject fake API responses and capture API calls
var mocks = {_known: {}}

if (process.env.NODE_ENV != 'production') {
    window.apiMocks = mocks
}
