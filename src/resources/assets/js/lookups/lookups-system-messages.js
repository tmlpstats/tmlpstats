import Api from '../api'
import Immutable from 'immutable'

import { lookupsData } from './manager'

const EMPTY_LIST = []

export const systemMessagesData = lookupsData.addScope({
    scope: 'system_messages',

    loadForRegion(regionId, section) {
        const scope = this
        return scope.attemptCached(section, (dispatch) => {
            const params = {section, region: regionId}
            return dispatch(scope.manager.load(params, {
                api: Api.Admin.System.regionSystemMessages,
                transformData(data) {
                    return Immutable.List(data)
                },
                successHandler(data) {
                    return scope.replaceItem(section, data)
                }
            }))
        })
    },

    getMessagesOnly(state, section) {
        return this.selector(state).get(section)
    },

    getMessages(state, section) {
        return this.getMessagesOnly(state, section) || EMPTY_LIST
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
