import { combineReducers } from 'redux'

import regionsReducer from './regions/reducers'

const adminReducer = combineReducers({
    regions: regionsReducer
})

export default adminReducer
