import Immutable from 'immutable'

import { createStore, applyMiddleware, thunk } from '../../testing-store'

import Api from '../../../api'
import ReportsMeta from '../../../reports/meta'
import { TabbedReportManager } from '../../../reports/tabbed_report/manager'

describe('TabbedReportManager', () => {
    let pages
    const fakeApi = jest.fn(() => Promise.resolve({pages: pages}))
    const manager = new TabbedReportManager({
        prefix: 'reports/LocalReport',
        findRoot: (state) => state,
        actions: {
            load: {
                api: fakeApi,
                setLoaded: true
            }
        }
    })

    describe('Reducer', () => {
        const reducer = manager.reducer()
        const store = createStore(reducer, undefined, applyMiddleware(thunk))
        const initial = reducer(undefined, {type: 'ignoreme'})

        test('Normal Sequence', () => {
            store.dispatch(manager.init(ReportsMeta['Local'], Immutable.Map()))

            pages = {RatingSummary: 'abcdefg'}
            let input = {foo: '9'}
            return store.dispatch(manager.loadReport('RatingSummary', input)).then(() => {
                expect(fakeApi).toHaveBeenCalledTimes(1)
                const firstCall = fakeApi.mock.calls[0]
                expect(firstCall[0].foo).toEqual('9')
                expect(firstCall[0].pages).toEqual(['RatingSummary'])
                expect(firstCall[1].loader).toBe(manager)
                const state = store.getState()
                expect(state.data.get('RatingSummary')).toEqual('abcdefg')
                expect(state.loader.done).toEqual(Immutable.Set.of('RatingSummary'))
            })
        })
    })
})
