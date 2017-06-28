import { lookupsData } from './manager'

const EMPTY_LIST = []

export const systemMessagesData = lookupsData.addScope({
    scope: 'system_messages',

    load(section) {
        return {} // TODO
    },

    getMessages(state, section) {
        let value = this.selector(state).get(section)
        return value || EMPTY_LIST
    }
})
