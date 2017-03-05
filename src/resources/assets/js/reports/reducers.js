import { combineReducers } from 'redux'

import globalReportReducer from './global_report/reducers'
import localReportReducer from './local_report/reducers'
import { reportConfig } from './data'

export default combineReducers({
    global_report: globalReportReducer,
    local_report: localReportReducer,
    reportConfig: reportConfig.reducer(),
})
