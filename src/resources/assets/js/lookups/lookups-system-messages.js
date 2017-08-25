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
    },

    dismiss(section, id) {
        return this.addProps({dismissed: true}, section, id)
    },

    addProps(extraProps, section, id) {
        return (dispatch, getState) => {
            const updatedValue = this
                .getMessages(getState(), section)
                .map((item) => {
                    if (item.id == id) {
                        return {...item, ...extraProps}
                    }
                    return item
                })
            return dispatch(this.replaceItem(section, updatedValue))
        }
    }
})
