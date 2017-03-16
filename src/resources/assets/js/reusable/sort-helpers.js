import Immutable from 'immutable'
import { createSelector } from 'reselect'

export const SORT_BY = 'sort_by'

export function compositeKey(pairs) {
    return (a, b) => {
        var n
        for (var i = 0; i < pairs.length; i++) {
            var key = pairs[i][0]

            switch (pairs[i][1]) {
            case 'string':
                n = a[key].localeCompare(b[key])
                break
            case 'number':
                n = a[key] - b[key]
                break
            }
            if (n != 0) {
                return n
            }

        }
        return 0
    }
}

export function createSorters(input) {
    let m = Immutable.Map()
    input.forEach((v) => {
        m = m.set(v.key, v)
    })
    return  m
}

const defaultInputSelectors = [
    (input) => input.data,
    (input) => input.meta.get(SORT_BY)
]

export function collectionSortSelector(sorters, inputs=defaultInputSelectors) {
    return createSelector(
        inputs,
        (data, sortKey) => {
            let values = Object.keys(data).map(k => data[k])
            const sorter = sorters.get(sortKey)
            if (sorter) {
                values.sort(sorter.comparator)
            } else {
                console.log('could not get sorter')
            }
            return values
        }
    )
}
