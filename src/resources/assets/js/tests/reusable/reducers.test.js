import { MessageManager, LoadingMultiState, InlineBulkWork } from '../../reusable/reducers'

describe('MessageManager', () => {
    let mgr = new MessageManager('foo')
    let beginState = {
        '123': [{id: 'FOO_BAR', level: 'error'}],
        '456': [{id: 'BAZ_QUUX', level: 'error'}],
        '789': []
    }
    describe('reducer', () => {
        let reducer = mgr.reducer()

        it('should replace a single message set', () => {
            let newValue = [{id: 'hello', level: 'error'}]
            let result = reducer(beginState, mgr.replace(789, newValue))
            expect(result[789]).toBe(newValue)
            expect(beginState[789]).toEqual([]) // Checks we did not clobber the original value (reducer is 'pure')
        })

        it('should bulk replace messages (replaceMany)', () => {
            let toReplace = {123: [], 789: beginState[456]}
            let result = reducer(beginState, mgr.replaceMany(toReplace))
            expect(result[123]).toEqual([])
            expect(result[789]).toBe(result[456])
        })

        it('Should replace entire collection', () => {
            let input = {125: beginState[456]}
            let result = reducer(beginState, mgr.replaceAll(input))
            expect(Object.keys(result)).toHaveLength(1)
            expect(result).toBe(input)
        })

        it('Should reset', () => {
            let result = reducer(beginState, mgr.reset())
            expect(Object.keys(result)).toHaveLength(0)
        })

        it('ignores irrelevant context', () => {
            let result = reducer(beginState, mgr.replaceAll({11: []}, 'ignored'))
            expect(result).toBe(beginState)
        })

        it('works with empty value', () => {
            let result = reducer(undefined, {type: 'irrelevant'})
            expect(Object.keys(result)).toHaveLength(0)
        })
    })
})

describe('LoadingMultiState', () => {
    const states = LoadingMultiState.states
    const lms = new LoadingMultiState('foo/bar')
    const reducer = lms.reducer()
    const loadState = lms.actionCreator()
    const beginState = states.new

    test('Action Creator Basic', () => {
        expect(loadState('loading').payload).toEqual('loading')
        expect(loadState('loading').type).toEqual('foo/bar')
    })

    describe('State replacements', () => {
        test('Initial State', () => {
            let result = reducer(undefined, {type: 'irrelevant'})
            expect(result).toBe(states.new)
        })

        test('Textual state change', () => {
            expect(reducer(beginState, loadState('loading'))).toBe(states.loading)
            expect(reducer(beginState, loadState('loaded'))).toBe(states.loaded)
        })

        test('Error state change', () => {
            let action = loadState({error: 'New Error value'})
            let result = reducer(beginState, action)
            expect(result).toBe(action.payload)
            expect(result.state).toEqual('failed')
            expect(result.available).toEqual(false)
            expect(result.error).toEqual('New Error value')
        })

        test('Advanced replacement mode', () => {
            let action = loadState('failed', {'foo': 'bar'})
            let payload = action.payload
            expect(payload.failed).toBe(true)
            expect(payload.foo).toEqual('bar')
        })
    })
})

describe('InlineBulkWork', () => {
    let ibw = new InlineBulkWork('some/prefix')

    describe('Actions Basic Properties', () => {
        let suite = [
            [ibw.mark(7), 'mark', (ac) => { expect(ac.payload.pk).toEqual(7) }],
            [ibw.beginWork(), 'begin_work'],
            [ibw.endWork(), 'end_work'],
            [ibw.clearWork(), 'clear_work']
        ]
        suite.forEach(function(item) {
            test(`Action ${item[1]}`, () => {
                const action = item[0]
                expect(action.type).toEqual('some/prefix')
                expect(action.payload.job).toEqual(item[1])
                if(item[2]) {
                    item[2](action)
                }
            })
        })
    })

    describe('Reducer', () => {
        const reducer = ibw.reducer()
        const initial = reducer(undefined, {type: 'bleh'})

        test('Default state', () => {
            let result = reducer(undefined, {type: 'irrelevant'})
            expect(result.ts).toEqual(0)
            expect(result.working).toBe(null)
        })

        test('Double mark does nothing', () => {
            let value = initial
            let value2 = reducer(value, ibw.mark('7'))
            expect(value2.changed[7]).toBe(1)
            let value3 = reducer(value2, ibw.mark('7'))
            expect(value3.changed[7]).toBe(1)
            expect(value3).toBe(value2)
        })

        test('Pattern of changes', () => {
            let values = [initial]
            function runIt(action) {
                const last = values[values.length - 1]
                values.push(reducer(last, action))
            }

            runIt(ibw.mark('7'))
            expect(values[1]).not.toBe(values[0])
            runIt(ibw.mark('8'))
            expect(values[2]).not.toBe(values[1])
            expect(values[2].changed['7']).toEqual(1)
            expect(values[2].changed['8']).toEqual(2)

            // running some work
            runIt(ibw.beginWork())
            expect(values[3].working).toBe(values[2].changed)

            // Change some more things
            runIt(ibw.mark('11'))
            runIt(ibw.mark('7'))
            expect(values[5].changed['11']).toEqual(4)
            expect(values[5].changed['7']).toEqual(5)

            // End work, ensuring things were completed.
            runIt(ibw.endWork())
            expect(values[6].working).toBe(null)
            expect(values[6].changed['8']).toBe(undefined)
            expect(values[6].changed['7']).toEqual(5)
        })
    })
})
