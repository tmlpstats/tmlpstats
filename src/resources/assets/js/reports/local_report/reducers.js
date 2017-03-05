import { combineReducers } from 'redux'

import { reportData } from './data'

export default combineReducers({
    reportData: reportData.reducer(),
})
