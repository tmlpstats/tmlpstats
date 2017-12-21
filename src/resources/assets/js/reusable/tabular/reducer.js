// This is the reducer for Tabular.
import Immutable from 'immutable'

import { CLICK_COLUMN, OVERRIDE_SORT } from './actions'
import { TableState, ColumnSort, adaptColumnSorts } from './types'


const R = new Immutable.Record({
    tables: Immutable.Map()
})
const startState = new R()

const EMPTY_TABLE_STATE = new TableState({})
const LAST = -1 // The -1'th element is syntactic sugar for last element in an immutable list.


function reducer(state=startState, action) {
    if (action.type.startsWith('tabular/')) {
        let current = state.tables.get(action.table, EMPTY_TABLE_STATE)
        if (action.type === CLICK_COLUMN) {
            let sortBy = current.sort_by
            if (sortBy.size && sortBy.get(LAST).column == action.column) {
                // If the last item in the sort is the column just clicked, simply flip asc/desc.
                const v = sortBy.get(LAST)
                sortBy = sortBy.set(LAST, v.set('direction', v.direction == 'asc'? 'desc' : 'asc'))
            } else {
                const newSort = new ColumnSort({column: action.column})
                if (action.modKey) {
                    // If a modifier key is pressed, then add to a compound sort.
                    sortBy = sortBy.push(newSort)
                } else {
                    // For a click without modifier keys, replace the whole list with a clean one.
                    sortBy = Immutable.List.of(newSort)
                }
            }
            current = current.set('sort_by', sortBy)
        } else if (action.type === OVERRIDE_SORT) {
            // override sort is a special action not given by our own component, instead done when
            // there is an action maybe to do a custom set of sorts.
            current = current.set('sort_by', adaptColumnSorts(action.sorts))
        }
        return state.setIn(['tables', action.table], current)
    }
    return state
}

export default reducer
