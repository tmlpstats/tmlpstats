import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'
import { scoreboardReducer } from './scoreboard/reducers'
import coreReducer from './core/reducers'
import classListReducer from './class_list/reducers'
import qaReducer from './qtr_accountabilities/reducers'

import clearWrapper from './core/clearWrapper'


const submissionReducerInternal = combineReducers({
    core: coreReducer,
    applications: applicationReducer,
    class_list: classListReducer,
    courses: courseReducer,
    scoreboard: scoreboardReducer,
    qtr_accountabilities: qaReducer
})

export const submissionReducer = clearWrapper(submissionReducerInternal, (state, action) => {
    return submissionReducerInternal({core: state.core}, action)
})
