import { combineReducers } from 'redux'
import { LoadingMultiState } from '../reducers'
import { delayDispatch } from '../dispatch'
import { objectAssign } from '../ponyfill'

const LOADER_DEFAULT_OPTS = {
    extraLMS: [],
    defaultState: {},
    actions: {},
    setLoaded: false,
    transformData: x => x,
    successHandler: (data, {loader}) =>{
        return loader.replaceCollection(data)
    }
}

/**
 * ReduxLoader is the combination of patterns of the things we most likely want/need around loading.
 *
 * It combines these things:
 *   - Simple action creator for loading something from AJAX
 *   - LoadingMultiState reducer/action creator
 *   - Simple way to load data from initial data
 */
export class ReduxLoader {
    constructor(opts) {
        if (!opts) {
            opts = {}
        }
        opts = objectAssign({}, LOADER_DEFAULT_OPTS, opts)
        this.opts = opts

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

    // Run a network action with defaults looking from various options.
    // For example, for the action 'load' it will look for the callable
    // named 'loader' and work with updating the state using action creator 'loadState'
    runNetworkAction(action, params=null, opts=null) {
        opts = this._mergeOpts(opts)
        let actionData
        if (opts.actions[action]) {
            actionData = objectAssign({}, opts, opts.actions[action])
        } else {
            actionData = objectAssign({api: opts.loader}, opts)
        }

        const loadAction = this[actionData.lmsName || `${action}State`] // loadState, saveState, etc

        return (dispatch, getState, extra) => {
            dispatch(loadAction('loading'))
            const info = {dispatch, getState, extra, loader: this}

            const success = (data) => {
                const tdata = actionData.transformData(data, info)
                const result = actionData.successHandler(tdata, info)
                if (result) {
                    dispatch(result)
                }
                if (actionData.setLoaded) {
                    dispatch(loadAction('loaded'))
                }
                return tdata
            }

            const failHandler = (err) => {
                if (!err.error) {
                    err = {error: err.message || err}
                }
                dispatch(loadAction(err))
                throw err
            }

            // loader, saver, etc
            return actionData.api(params, info).then(success, failHandler)
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
        // add in all the LoadingMultiState reducers
        for (var k in this._lms) {
            reducerMap[k] = this._lms[k].reducer()
        }
        objectAssign(reducerMap, this._extraReducers(opts))
        return combineReducers(reducerMap)
    }

    _extraReducers() {

    }
}

export function rebindActionCreators(actions, obj) {
    var actionCreators = {}
    actions.forEach((key) => {
        actionCreators[key] = obj[key].bind(obj)
    })
    return actionCreators
}
