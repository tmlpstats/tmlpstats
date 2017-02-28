import Api from '../../api'
import { PAGES_CONFIG } from '../core/data'
import { MessageManager } from '../../reusable/reducers'

// bogusManager is used to cheat on dispatching to all the other MessageManager(s) all over the codebase
const bogusManager = new MessageManager('bogus')

export function getValidationMessages(center, reportingDate) {
    return (dispatch) => {
        return Api.ValidationData.validate({center, reportingDate}).then((data) => {
            dispatch(setMessages(data.messages))
            PAGES_CONFIG.forEach((config) => {
                // For each page, send the values to the downstream message reducers so that when we
                // follow a link over, the messages are there on that page. This requires a bit of shuffling, but worthwhile.
                const messages = data.messages[config.className]
                if (messages) {
                    let keyedMessages = {}
                    messages.forEach((message) => {
                        const k = message.reference.id
                        if (!keyedMessages[k]) {
                            keyedMessages[k] = []
                        }
                        keyedMessages[k].push(message)
                    })
                    dispatch(bogusManager.replaceAll(keyedMessages, config.key))
                }
            })
            return data
        })
    }
}

export function submitReport(center, reportingDate, data) {
    return (dispatch) => {
        return Api.SubmissionCore.completeSubmission({center, reportingDate, data})
    }
}

export function setMessages(messages) {
    return {
        type: 'review/setMessages',
        payload: messages
    }
}

export function setSubmitData(data) {
    return {
        type: 'review/setSubmitData',
        payload: data
    }
}

export function setSubmitResults(results) {
    return {
        type: 'review/setSubmitResults',
        payload: results
    }
}

export function setPreSubmitModal(show) {
    return {
        type: 'review/setPreSubmitModal',
        payload: show
    }
}

export function setPostSubmitModal(show) {
    return {
        type: 'review/setPostSubmitModal',
        payload: show
    }
}
