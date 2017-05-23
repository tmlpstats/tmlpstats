import Immutable from 'immutable'

import { objectAssign } from '../ponyfill'
import { ReduxLoader, LOADER_DEFAULT_OPTS } from './base'

const DEFAULTS = objectAssign({}, LOADER_DEFAULT_OPTS, {
    defaultState: Immutable.Map()
})

export default class ImmutableMapLoader extends ReduxLoader {
    constructor(opts) {
        super(opts, DEFAULTS)
        this.replace_action = opts.prefix + '/replace'
        this.replace_items_action = opts.prefix + '/replace_items'
        this.replace_item_action = opts.prefix + '/replace_item'
    }

    dataReducer(opts) {
        const { defaultState } = opts
        return (state=defaultState, action) => {
            switch (action.type) {
            case this.replace_action:
                return Immutable.Map(action.payload)
            case this.replace_item_action:
                return state.set(action.payload[0], action.payload[1])
            case this.replace_items_action:
                return state.merge(action.payload)
            }
            return state
        }
    }

    replaceCollection(value) {
        return {type: this.replace_action, payload: value}
    }

    replaceItem(key, value) {
        return {type: this.replace_item_action, payload: [key, value]}
    }

    replaceItems(values) {
        return {type: this.replace_items_action, payload: values}
    }
}
