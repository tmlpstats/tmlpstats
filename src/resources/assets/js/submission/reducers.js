import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'
import { scoreboardReducer } from './scoreboard/reducers'
import coreReducer from './core/reducers'
import clearWrapper from './core/clearWrapper'


const submissionReducerInternal = combineReducers({
    core: coreReducer,
    applications: applicationReducer,
    courses: courseReducer,
    scoreboard: scoreboardReducer
})

export const submissionReducer = clearWrapper(submissionReducerInternal, (state, action) => {
    return submissionReducerInternal({core: state.core}, action)
})
