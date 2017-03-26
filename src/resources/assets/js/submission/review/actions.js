import Api from '../../api'
import { PAGES_CONFIG } from '../core/data'
import { MessageManager } from '../../reusable/reducers'
import { reportSubmitting, displayFlow, reviewLoaded, DISPLAY_STATES } from './data'

// bogusManager is used to cheat on dispatching to all the other MessageManager(s) all over the codebase
const bogusManager = new MessageManager('bogus')

export const submitState = reportSubmitting.actionCreator()
export const displayState = displayFlow.actionCreator()
const loadState = reviewLoaded.actionCreator()
const STALE_TIMEOUT = 180 * 1000 // 3min(180 seconds) for validation messages to be stale.

export function getValidationMessages(center, reportingDate) {
    return (dispatch) => {
        dispatch(loadState('loading'))
        return Api.ValidationData.validate({center, reportingDate}).then((data) => {
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
            dispatch(loadState('loaded', {timestamp: Date.now()}))
            return data
        }).catch((err) => {
            dispatch(loadState({error: err.error || 'error'}))
        })
    }
}

// This thunk action has two functions:
// 1) Prevents getting validation messages twice due to being triggered multiple places
// 2) Also prevents getting messages on tab switches if messages are not stale
export function getValidationMessagesIfStale(center, reportingDate) {
    return (dispatch, getState) => {
        let checkNum = 0
        function check() {
            let current = reviewLoaded.selector(getState())
            if (!current.available) {
                return // Nothing to do if we're loading already or failed
            } else if (++checkNum < 2) {
                // This countdown is done because it's possible for simultaneous dispatch
                // to cause two different checks to observe validation messages as stale.
                setTimeout(check, 1)
            } else if (current.state == 'new' || (current.timestamp + STALE_TIMEOUT) < Date.now()) {
                dispatch(getValidationMessages(center, reportingDate))
            }
        }
        check()
    }
}

export function markStale() {
    return (dispatch, getState) => {
        let current = reviewLoaded.selector(getState())
        if (current.timestamp) {
            dispatch(loadState('loaded', {timestamp: 0}))
        }
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

export function setSubmitResults(results) {
    return {
        type: 'review/setSubmitResults',
        payload: results
    }
}
