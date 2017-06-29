import { combineReducers } from 'redux'

import { quartersData } from './data'
import regionsReducer from './regions/reducers'
import systemReducer from './system/reducers'

const lookupsReducer = combineReducers({
    quarters: quartersData.reducer()
})

const adminReducer = combineReducers({
    regions: regionsReducer,
    lookups: lookupsReducer,
    system: systemReducer
})

export default adminReducer
