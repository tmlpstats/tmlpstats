import { combineReducers } from 'redux'

import globalReportReducer from './global_report/reducers'
import localReportReducer from './local_report/reducers'

function showTokenReducer(state=false, action) {
    if (action.type == 'reports/showToken') {
        return action.payload
    }
    return state
}

export default combineReducers({
    global_report: globalReportReducer,
    local_report: localReportReducer,
    showToken: showTokenReducer,
})
