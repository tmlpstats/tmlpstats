import ImmutableMapLoader from '../../reusable/redux_loader/ImmutableMapLoader'
import { objectAssign, Promise } from '../../reusable/ponyfill'
import Immutable from 'immutable'

const MAX_PAGES = 5

const LoaderState = Immutable.Record({
    queue: Immutable.OrderedSet(),
    inprogress: Immutable.Set(),
    done: Immutable.Set(),
    baseKey: null,
    perLoad: 1
})

export class TabbedReportManager extends ImmutableMapLoader {
    constructor(opts) {
        super(opts)
        this.init_action = opts.prefix + '/init'
        this.queue_action = opts.prefix + '/queue'
        this.start_action = opts.prefix + '/start'
        this.finish_action = opts.prefix + '/finish'
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
                    const { reportRoot, baseKey } = action.payload
                    if (baseKey !== state.baseKey) {
                        state = state.set('baseKey', baseKey).delete('done')
                    }
                    // On initialize, we want the original priorities to stay ahead, so we subtract and re-add them.
                    let result = this._queueFromReport(reportRoot)
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

    loadReport(reportId, params, baseKey) {
        const { findRoot } = this.opts

        return (dispatch, getState) => {
            let data = findRoot(getState()).loader
            if (data.done.has(reportId)) {
                return Promise.resolve(null)
            }
            dispatch({type: this.queue_action, payload: reportId})
            return this.runLoadFlow(dispatch, getState, params, baseKey)
        }
    }

    runLoadFlow(dispatch, getState, globalParams, baseKey) {
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
                        let toUpdate = Immutable.Map()
                        let finishedPages = Immutable.Set()
                        pages.forEach((reportId) => {
                            let pageData = data.pages[reportId]
                            if (pageData !== undefined) {
                                let key = (baseKey)? baseKey.set('page', reportId) : reportId
                                toUpdate = toUpdate.set(key, pageData)
                                finishedPages = finishedPages.add(reportId)
                            }
                        })
                        if (toUpdate.size) {
                            dispatch(this.replaceItems(toUpdate))
                            dispatch({type: this.finish_action, payload: finishedPages})
                        }
                        setTimeout(flow, 200)
                    }
                }))
            }
        }
        return flow()
    }

    // replaceCollection, replaceItem, replaceItems inherited from ImmutableMapLoader

    init(reportRoot, baseKey) {
        return {type: this.init_action, payload: {reportRoot, baseKey}}
    }
}
