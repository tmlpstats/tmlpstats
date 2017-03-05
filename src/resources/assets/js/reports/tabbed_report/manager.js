import { ReduxLoader } from '../../reusable/redux_loader/base'
import { objectAssign, Promise } from '../../reusable/ponyfill'
import Immutable from 'immutable'

const MAX_PAGES = 5

const LoaderState = Immutable.Record({
    queue: Immutable.OrderedSet(),
    inprogress: null,
    done: Immutable.Map(),
    perLoad: 1
})

export class TabbedReportManager extends ReduxLoader {
    constructor(opts) {
        super(opts)
        this.queue_action = opts.prefix + '/queue'
        this.start_action = opts.prefix + '/start'
        this.finish_action = opts.prefix + '/finish'
        this.replace_action = opts.prefix + '/replace'
        this.replace_items_action = opts.prefix + '/replace_items'
    }

    extraReducers(opts) {
        const loadQueue = Immutable.Map(opts.report)
                .valueSeq()
                .filter((report) => report.type == 'report')
                .sortBy((report) => report.n)
                .map((report) => report.id)
        const defaultState = new LoaderState({queue: Immutable.OrderedSet(loadQueue)})

        const lsReducer = (state=defaultState, action) => {
            if (action.type.startsWith(opts.prefix)) {
                if (action.type == this.queue_action) {
                    let id = action.payload
                    if (!state.done.has(id)) {
                        // Push the element to the end of the queue which would be the highest priority
                        state = state.set('queue', state.queue.remove(id).add(id))
                    }
                } else if (action.type == this.start_action) {
                    let inprogress = state.queue.slice(-state.perLoad).toSet()
                    let queue = state.queue.subtract(inprogress)
                    state = state.set('queue', queue).set('inprogress', inprogress)
                } else if (action.type == this.finish_action) {
                    let changes = Immutable.Map(action.payload)
                    let keys = changes.keySeq()
                    console.log('finished loading', keys.toJS())
                    let newData = {
                        done: state.done.merge(changes),
                        inprogress: state.inprogress.subtract(keys),
                        queue: state.queue.subtract(keys),
                        perLoad: Math.min(MAX_PAGES, state.perLoad + 1)
                    }
                    state = state.merge(newData)
                }
            }
            return state
        }
        return {loader: lsReducer}
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
                        dispatch({type: this.finish_action, payload: toUpdate})
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
}
