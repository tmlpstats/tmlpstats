import { combineReducers } from 'redux'

import globalReportReducer from './global_report/reducers'

export default combineReducers({
    global_report: globalReportReducer
})
