import Immutable from 'immutable'
import { createSelector } from 'reselect'

export const SORT_BY = 'sort_by'

const SORTERS = {
    string(selector) {
        return function(a, b) {
            const valA = selector(a)
            if (valA === null || valA === undefined) {
                return -1
            }
            return valA.localeCompare(selector(b))
        }
    },

    number(selector) {
        return function(a, b) {
            return selector(a) - selector(b)
        }
    },

    moment(selector) {
        return function(a, b) {
            const valA = selector(a)
            const valB = selector(b)
            if (!valA) {
                return 1
            } else if (!valB) {
                return -1
            }
            if (valA.isBefore(valB)) {
                return -1
            } else if (valA.isAfter(valB)) {
                return 1
            } else {
                return 0
            }
        }
    }

}

export function compositeKey(pairs) {
    const subsorters = pairs.map((x) => {
        const selector = (typeof x[0] === 'string')? createIteratee(x[0]) : x[0]

        return SORTERS[x[1]](selector)
    })
    return (a, b) => {
        var n
        for (var i = 0; i < subsorters.length; i++) {
            n = subsorters[i](a, b)
            if (n != 0) {
                return (pairs[i][2] == 'desc') ? -n : n
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
