import * as selectors from '../../submission/core/selectors'

describe('selectors', () => {
    describe('makeAccountabilitiesSelector', () => {
        it('should select accountabilities properly', () => {
            let accSelector = selectors.makeAccountabilitiesSelector('team')
            expect(accSelector).toBeDefined()

            let lookups = {
                accountabilities: {
                    123: {'name': 'foo', 'context': 'team'},
                    456: {'name': 'skip', 'context': 'unused'},
                    789: {'name': 'bar', 'context': 'team'}
                },
                orderedAccountabilities: ['123', '456', '789']
            }

            let result = accSelector({submission: {core: {lookups: lookups}}})
            expect(result.length).toBe(2)
            expect(result[0].name).toBe('foo')
            expect(result[1].name).toBe('bar')
        })

    })

    describe('makeQuartersSelector', () => {
        it('should at least return a selector', () => {
            let qSelector = selectors.makeQuartersSelector('foo')
            expect(qSelector).toBeDefined()
        })
    })
})
