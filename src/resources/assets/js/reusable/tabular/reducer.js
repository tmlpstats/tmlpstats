// This is the reducer for Tabular.
import Immutable from 'immutable'

import { CLICK_COLUMN, CLICK_ITEM } from './actions'
import { TableState } from './types'


const R = new Immutable.Record({
    tables: Immutable.Map()
})
const startState = new R()

const EMPTY_TABLE_STATE = new TableState({})


function reducer(state=startState, action) {
    if (action.type.startsWith('tabular/')) {
        let current = state.tables.get(action.table, EMPTY_TABLE_STATE)
        if (action.type == CLICK_COLUMN) {
            let sortBy = current.sort_by
            if (sortBy.size && sortBy.get(-1)[0] == action.column) {
                const v = sortBy.get(-1)
                sortBy = sortBy.set(-1, [v[0], v[1] == 'asc'? 'desc' : 'asc'])
            } else if (action.modKey) {
                sortBy = sortBy.push([action.column, 'asc'])
            } else {
                sortBy = Immutable.List.of([action.column, 'asc'])
            }
            return state.setIn(['tables', action.table], current.set('sort_by', sortBy))
        }
    }
    return state
}

export default reducer
