import Immutable from 'immutable'
import { combineReducers } from 'redux'

import { LoadingMultiState, MessageManager } from '../reducers'
import { delayDispatch } from '../dispatch'
import { objectAssign } from '../ponyfill'

export const LOADER_DEFAULT_OPTS = {
    extraLMS: [],
    defaultState: {},
    actions: {},
    setLoaded: false,
    messageManager: false,
    initialMeta: Immutable.Map(),
    transformData: x => x,
    successHandler: (data, {loader}) =>{
        return loader.replaceCollection(data)
    },
    failHandler: (err) =>{
        throw err
    }
}

const SET_METADATA = 'loader/setMetadata'

/**
 * ReduxLoader is the combination of patterns of the things we most likely want/need around loading.
 *
 * It combines these things:
 *   - Simple action creator for loading something from AJAX
 *   - LoadingMultiState reducer/action creator
 *   - Simple way to load data from initial data
 *   - Optional MessageManager
 */
export class ReduxLoader {
    constructor(opts, _defaults=LOADER_DEFAULT_OPTS) {
        if (!opts) {
            opts = {}
        }
        opts = objectAssign({}, _defaults, opts)
        this.opts = opts

        if (opts.messageManager) {
            this.messages = new MessageManager(opts.prefix + '/messages')
        }

        // Create all the LoadingMultiState related to us.
        var loaders = this._lms = {}
        const ls = ['loadState']
        ls.concat(opts.extraLMS).forEach((loaderName) => {
            const l = loaders[loaderName] = new LoadingMultiState(opts.prefix + '/' + loaderName)
            this[loaderName] = l.actionCreator()
        })
    }

    _mergeOpts(opts) {
        return opts? objectAssign({}, this.opts, opts) : this.opts
    }

    _actionData(action, opts) {
        let actionData
        if (opts.actions[action]) {
            actionData = objectAssign({}, opts, opts.actions[action])
        } else {
            actionData = objectAssign({api: opts.loader}, opts)
        }
        actionData.loadActionCreator = this[actionData.lmsName || `${action}State`]
        return actionData
    }


    runInGroup(dispatch, action, handler) {
        this.opts.actions[action].inGroup = true
        const actionData = this._actionData(action, this.opts)
        const loadAction = actionData.loadActionCreator
        dispatch(loadAction('loading'))
        return handler().then(
            (result) => {
                this.opts.actions[action].inGroup = false
                dispatch(loadAction('loaded'))
                return result
            },
            (err) => {
                this.opts.actions[action].inGroup = false
                const fixedErr = (err.error)? err : {error: err.message || err}
                dispatch(loadAction(fixedErr))
                throw err  // re-throw the error so 'real' catch handlers can do their work
            }
        )
    }

    // Run a network action with defaults looking from various options.
    // For example, for the action 'load' it will look for the callable
    // named 'loader' and work with updating the state using action creator 'loadState'
    runNetworkAction(action, params=null, opts=null) {
        opts = this._mergeOpts(opts)

        const actionData = this._actionData(action, opts)
        const loadAction = actionData.loadActionCreator

        return (dispatch, getState, extra) => {
            if (!actionData.inGroup) {
                dispatch(loadAction('loading'))
            }
            const info = {dispatch, getState, extra, params, loader: this}

            const success = (data) => {
                const tdata = actionData.transformData(data, info)
                const result = actionData.successHandler(tdata, info)
                if (result) {
                    dispatch(result)
                }
                if (actionData.setLoaded && !actionData.inGroup) {
                    dispatch(loadAction('loaded'))
                }
                return tdata
            }

            const fail = (err) => {
                const fixedErr = (err.error) ? err : {error: err.message || err}
                const result = actionData.failHandler(fixedErr, info)
                if (result) {
                    dispatch(result)
                }
                if (actionData.setLoaded && !actionData.inGroup) {
                    dispatch(loadAction('loaded'))
                }
            }

            // loader, saver, etc
            return actionData.api(params, info).then(success, fail)
        }
    }

    load(...args) {
        return this.runNetworkAction('load', ...args)
    }

    conditionalLoad(dispatch, loadState, ...loadParams) {
        if(loadState.loadState) {
            loadState = loadState.loadState
        }
        if (loadState.state == 'new') {
            delayDispatch(dispatch, this.load(...loadParams))
            return false
        }
        return (loadState.state == 'loaded')
    }

    /** A shortcut action creator for clearing out the whole collection and clearing load state. */
    resetAll() {
        return (dispatch) => {
            dispatch(this.loadState('new'))
            dispatch(this.replaceCollection(this.opts.defaultState))
        }
    }

    reducer(opts=null) {
        opts = this._mergeOpts(opts)
        var reducerMap = {}

        if (!opts.excludeDataReducer) {
            reducerMap.data = this.dataReducer(opts)
        }
        if (opts.messageManager) {
            reducerMap.messages = this.messages.reducer()
        }
        if (opts.useMeta) {
            reducerMap.meta = metaReducer(opts.prefix, opts.initialMeta)
        }
        // add in all the LoadingMultiState reducers
        for (var k in this._lms) {
            reducerMap[k] = this._lms[k].reducer()
        }
        objectAssign(reducerMap, this.extraReducers(opts))
        return combineReducers(reducerMap)
    }

    extraReducers() {

    }

    setMeta(key, value) {
        const { prefix } = this.opts
        return {type: SET_METADATA, payload: { prefix, key, value }}
    }
}

export function rebindActionCreators(actions, obj) {
    var actionCreators = {}
    actions.forEach((key) => {
        actionCreators[key] = obj[key].bind(obj)
    })
    return actionCreators
}


function metaReducer(prefix, initialMeta) {
    return (state=initialMeta, action) => {
        if (action.type == SET_METADATA && action.payload.prefix == prefix) {
            const { key, value } = action.payload
            return state.set(key, value)
        }
        return state
    }
}
