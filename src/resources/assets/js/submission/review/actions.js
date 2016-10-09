import Api from '../../api'
import { PAGES_CONFIG } from '../core/data'
import { MessageManager } from '../../reusable/reducers'

// bogusManager is used to cheat on dispatching to all the other MessageManager(s) all over the codebase
const bogusManager = new MessageManager('bogus')

export function getValidationMessages(center, reportingDate) {
    return (dispatch) => {
        return Api.ValidationData.validate({center, reportingDate}).then((data) => {
            console.log(data)
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
            console.log('finished setup')
            return data
        }).catch((err) => {
            console.log(err)
        })
    }
}

export function setMessages(messages) {
    return {
        type: 'review/setMessages',
        payload: messages
    }
}
