/**
 * This is an experiment, to see if we can do a single reducer for a whole variety of lookups, by using
 * keyed data in a reasonably 'flat' immutable map.
 *
 *
 * The primary use of this is to have data for the purpose of resolving where it's done without as much mess as our normal
 * system of doing so, primarily because we're not setting up reducers all over the place.
 *
 * My suspicion is that where this will lead is towards something like GraphQL/Relay, but
 * this gives us a way to test that assumption first.
 */
import Immutable from 'immutable'

import { objectAssign, Promise } from '../reusable/ponyfill'
import { ReduxLoader, LOADER_DEFAULT_OPTS } from '../reusable/redux_loader/base'
import storeProxy from '../storeProxy'

const EMPTY_MAP = Immutable.Map()

const DEFAULTS = objectAssign({}, LOADER_DEFAULT_OPTS, {
    defaultState: EMPTY_MAP,
    setLoaded: true
})

export class LookupManager extends ReduxLoader {
    constructor(opts) {
        super(opts, DEFAULTS)
        this.put_items_action = opts.prefix + '/put_items'
        this.put_item_action = opts.prefix + '/put_item'
        this.replace_scope_action = opts.prefix + '/replace_scope'
        this.init_scope_action = opts.prefix + '/init_scope'
        this.scopes = {}
    }

    dataReducer(opts) {
        const { defaultState } = opts
        return (state=defaultState, action) => {
            const payload = action.payload
            if (payload && payload.scope) {
                const scope = payload.scope
                if (action.type == this.put_items_action) {
                    const { values } = payload
                    let collection = state.get(scope) || EMPTY_MAP
                    if (Immutable.Iterable.isKeyed(values)) {
                        collection = collection.merge(values)
                    } else {
                        values.forEach((pair) => {
                            collection = collection.set(pair[0], pair[1])
                        })
                    }
                    return state.set(scope, collection)
                } else if (action.type == this.put_item_action) {
                    return state.setIn([scope, payload.key], payload.value)
                } else if (action.type == this.replace_scope_action) {
                    return state.set(scope, Immutable.Map(payload.value))
                }
            }
            return state
        }
    }

    replaceScope(scope, value) {
        return {type: this.replace_scope, payload: {scope, value}}
    }

    putItems(scope, values) {
        return {type: this.put_items_action, payload: {scope, values}}
    }

    putItem(scope, key, value) {
        return {type: this.put_item_action, payload: {scope, key, value}}
    }

    ensureScope(scope) {
        // Sneaky action creator reuses a different one
        return this.putItems(scope, [])
    }

    addScope(props) {
        props.manager = this
        const handler = new ScopedLookup(props)
        this.scopes[props.scope] = handler
        this.opts.defaultState = this.opts.defaultState.set(props.scope, EMPTY_MAP)
        storeProxy.dispatch({type: this.init_scope_action, payload: props.scope})
        return handler
    }
}

export class ScopedLookup {
    constructor(props) {
        if (!props.scope) {
            // TODO ERROR
        }
        objectAssign(this, props)
    }

    getKey(obj) {
        return obj.id
    }

    replaceCollection(value) {
        return lookupsData.replaceScope(this.scope, value)
    }

    replaceItem(key, value) {
        if (!value) {
            value = key
            key = this.getKey(value)
        }
        return lookupsData.putItem(this.scope, key, value)
    }

    replaceItems(values) {
        return lookupsData.putItems(this.scope, values)
    }

    selector(state) {
        return state.lookups.data.get(this.scope) || EMPTY_MAP
    }

    attemptCached(id, thunk) {
        return (dispatch, getState) => {
            const overall = this.selector(getState())
            if (overall && overall.get(id)) {
                return Promise.resolve(overall.get(id))
            }
            return thunk(dispatch, getState)
        }
    }
}

export const lookupsData = new LookupManager({
    prefix: 'lookups',
    extraLMS: ['saveState']
})
