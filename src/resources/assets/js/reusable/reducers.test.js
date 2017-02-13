import { MessageManager } from './reducers'

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

        // TODO replaceAll
        // TODO reset
    })
})
