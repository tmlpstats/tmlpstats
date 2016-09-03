import { combineReducers } from 'redux'
import { LoadingMultiState } from '../reducers'
import { bestErrorValue } from '../ajax_utils'

import { objectAssign } from '../ponyfill'

const LOADER_DEFAULT_OPTS = {
    extraLMS: [],
    defaultState: {},
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

    load(params=null, opts=null) {
        opts = this._mergeOpts(opts)

        return (dispatch, getState, extra) => {
            dispatch(this.loadState('loading'))
            const info = {dispatch, getState, extra, loader: this}
            return this.opts.loader(params, info).done((data) => {
                if (data.error) {
                    dispatch(this.loadState({error: data.error || 'error'}))
                } else {
                    const tdata = opts.transformData(data, info)
                    const result = opts.successHandler(tdata, info)
                    if (result) {
                        dispatch(result)
                    }
                    if (opts.setLoaded) {
                        dispatch(this.loadState('loaded'))
                    }
                }
            }).fail((xhr, textStatus) => {
                dispatch(this.loadState({error: bestErrorValue(xhr, textStatus)}))
            })
        }
    }

    conditionalLoad(dispatch, data, loadParams = null) {
        const { loading } = data
        if (loading.state == 'new') {
            setTimeout(() => {
                dispatch(this.load(loadParams))
            })
            return false
        }
        return (loading.state == 'loaded')
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
        var reducerMap = {
            data: this.dataReducer(opts)
        }
        // add in all the LoadingMultiState reducers
        for (var k in this._lms) {
            reducerMap[k] = this._lms[k].reducer()
        }
        return combineReducers(reducerMap)
    }
}

export function rebindActionCreators(actions, obj) {
    var actionCreators = {}
    actions.forEach((key) => {
        actionCreators[key] = obj[key].bind(obj)
    })
    return actionCreators
}
