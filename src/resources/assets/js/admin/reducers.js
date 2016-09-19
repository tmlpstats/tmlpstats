import { combineReducers } from 'redux'

import { quartersData } from './data'
import regionsReducer from './regions/reducers'

const lookupsReducer = combineReducers({
    quarters: quartersData.reducer()
})

const adminReducer = combineReducers({
    regions: regionsReducer,
    lookups: lookupsReducer
})

export default adminReducer
