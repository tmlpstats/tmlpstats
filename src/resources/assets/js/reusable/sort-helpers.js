import Immutable from 'immutable'
import { createSelector } from 'reselect'

export const SORT_BY = 'sort_by'

export function compositeKey(pairs) {
    pairs = pairs.map((x) => {
        if (typeof x[0] === 'string') {
            return [createIteratee(x[0]), x[1]]
        }
        return x
    })
    return (a, b) => {
        var n
        for (var i = 0; i < pairs.length; i++) {
            const spec = pairs[i]
            const getKey = spec[0]

            switch (spec[1]) {
            case 'string':
                n = getKey(a).localeCompare(getKey(b))
                break
            case 'number':
                n = getKey(a) - getKey(b)
                break
            case 'moment':
                if (getKey(a).isBefore(getKey(b))) {
                    n = -1
                } else if (getKey(a).isAfter(getKey(b))) {
                    n = 1
                } else {
                    n = 0
                }
                break
            }
            if (n != 0) {
                return (spec[2] == 'desc') ? -n : n
            }
        }
        return 0
    }
}

function createIteratee(key) {
    return (obj) => obj[key]
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
