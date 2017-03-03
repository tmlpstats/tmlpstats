import Api from '../../api'
import { PAGES_CONFIG } from '../core/data'
import { MessageManager } from '../../reusable/reducers'
import { reportSubmitting, displayFlow, DISPLAY_STATES } from './data'

// bogusManager is used to cheat on dispatching to all the other MessageManager(s) all over the codebase
const bogusManager = new MessageManager('bogus')

export const submitState = reportSubmitting.actionCreator()
export const displayState = displayFlow.actionCreator()

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
        dispatch(submitState('loading'))
        return Api.SubmissionCore.completeSubmission({center, reportingDate, data}).then((result) => {
            if (!result) {
                throw new Error('Failed to submit report. Please try again.')
            }

            if (!result.success) {
                throw new Error(result.message)
            }

            dispatch(setSubmitResults({
                message: result.message,
                submittedAt: result.submittedAt,
                isSuccess: true,
            }))
            dispatch(submitState('loaded'))
            dispatch(displayState(DISPLAY_STATES.postSubmit))
        }).catch((err) => {
            dispatch(setSubmitResults({
                message: err,
                isSuccess: false,
            }))
            dispatch(submitState('failed'))
            dispatch(displayState(DISPLAY_STATES.postSubmit))
        })
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
