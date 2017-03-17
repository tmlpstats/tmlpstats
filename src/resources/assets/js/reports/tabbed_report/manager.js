import { ReduxLoader } from '../../reusable/redux_loader/base'
import { objectAssign, Promise } from '../../reusable/ponyfill'
import Immutable from 'immutable'

const MAX_PAGES = 5

const LoaderState = Immutable.Record({
    queue: Immutable.OrderedSet(),
    inprogress: Immutable.Set(),
    done: Immutable.Set(),
    perLoad: 1
})

if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position) {
        position = position || 0
        return this.substr(position, searchString.length) === searchString
    }
}

export class TabbedReportManager extends ReduxLoader {
    constructor(opts) {
        super(opts)
        this.init_action = opts.prefix + '/init'
        this.queue_action = opts.prefix + '/queue'
        this.start_action = opts.prefix + '/start'
        this.finish_action = opts.prefix + '/finish'
        this.replace_action = opts.prefix + '/replace'
        this.replace_items_action = opts.prefix + '/replace_items'
    }

    extraReducers(opts) {
        let init = {}
        if (opts.report) {
            init.queue = this._queueFromReport(opts.report)
        }

        const defaultState = new LoaderState(init)

        const lsReducer = (state=defaultState, action) => {
            if (action.type.startsWith(opts.prefix)) {
                if (action.type == this.queue_action) {
                    let id = action.payload
                    if (!state.done.has(id) && !state.inprogress.has(id)) {
                        // Push the element to the end of the queue which would be the highest priority
                        state = state.set('queue', state.queue.remove(id).add(id))
                    }
                } else if (action.type == this.start_action) {
                    let inprogress = state.queue.slice(-state.perLoad).toSet()
                    let queue = state.queue.subtract(inprogress)
                    state = state.set('queue', queue).set('inprogress', inprogress)
                } else if (action.type == this.finish_action) {
                    let changes = Immutable.Set(action.payload)
                    console.log('finished loading', changes.toJS())
                    let newData = {
                        done: state.done.union(changes),
                        inprogress: Immutable.Set(),
                        queue: state.queue.subtract(changes),
                        perLoad: Math.min(MAX_PAGES, state.perLoad + 1)
                    }
                    state = state.merge(newData)
                } else if (action.type == this.init_action) {
                    // On initialize, we want the original priorities to stay ahead, so we subtract and re-add them.
                    let result = this._queueFromReport(action.payload)
                    result = result.subtract(state.inprogress).subtract(state.done).subtract(state.queue).union(state.queue)
                    state = state.set('queue', result)
                }
            }
            return state
        }
        return {loader: lsReducer}
    }

    _queueFromReport(input) {
        return Immutable.Map(input)
            .valueSeq()
            .filter((report) => report.type == 'report')
            .sortBy((report) => report.n)
            .map((report) => report.id)
            .toOrderedSet()
    }

    // Separated out because then it's a bit less boilerplate to manage.
    dataReducer(opts) {
        const { defaultState } = opts
        return (state=defaultState, action) => {
            switch (action.type) {
            case this.replace_action:
                return action.payload
            case this.replace_items_action:
                return objectAssign({}, state, action.payload)
            }
            return state
        }
    }

    loadReport(reportId, params) {
        const { findRoot } = this.opts

        return (dispatch, getState) => {
            let data = findRoot(getState()).loader
            if (data.done.has(reportId)) {
                return Promise.resolve(null)
            }
            dispatch({type: this.queue_action, payload: reportId})
            return this.runLoadFlow(dispatch, getState, params)
        }
    }

    runLoadFlow(dispatch, getState, globalParams) {
        const { findRoot } = this.opts
        let getData = () => findRoot(getState()).loader

        let flow = () => {
            let tmpData = getData()
            if (!tmpData.inprogress || !tmpData.inprogress.size && tmpData.queue && tmpData.queue.size) {
                dispatch({type: this.start_action, payload: null})

                let pages = getData().inprogress.toArray()
                let params = objectAssign({pages: pages}, globalParams)
                return dispatch(this.runNetworkAction('load', params, {
                    successHandler: (data) => {
                        let toUpdate = {}
                        pages.forEach((reportId) => {
                            toUpdate[reportId] = data.pages[reportId]
                        })
                        dispatch({type: this.replace_items_action, payload: toUpdate})
                        dispatch({type: this.finish_action, payload: pages})
                        setTimeout(flow, 200)
                    }
                }))
            }
        }
        return flow()
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

    init(reportRoot) {
        return {type: this.init_action, payload: reportRoot}
    }
}
