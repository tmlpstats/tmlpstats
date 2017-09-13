import _ from 'lodash'

import { objectAssign } from '../ponyfill'
import { ReduxLoader } from './base'

export default class SimpleReduxLoader extends ReduxLoader {
    constructor(opts) {
        super(opts)
        this.replace_action = opts.prefix + '/replace'
        this.replace_items_action = opts.prefix + '/replace_items'
        this.delete_item_action = opts.prefix + '/delete'
    }

    dataReducer(opts) {
        const { defaultState } = opts
        return (state=defaultState, action) => {
            switch (action.type) {
            case this.replace_action:
                return action.payload
            case this.replace_items_action:
                return objectAssign({}, state, action.payload)
            case this.delete_item_action:
                return _.omit(state, [action.payload])
            }
            return state
        }
    }

    replaceCollection(value) {
        return {type: this.replace_action, payload: value}
    }

    replaceItem(key, value) {
        return {type: this.replace_items_action, payload: {[key]: value}}
    }

    replaceItems(values) {
        return {type: this.replace_items_action, payload: values}
    }

    deleteItem(key) {
        return {type: this.delete_item_action, payload: key}
    }
}
