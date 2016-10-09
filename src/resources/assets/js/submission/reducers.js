import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'
import { scoreboardReducer } from './scoreboard/reducers'
import coreReducer from './core/reducers'
import teamMembersReducer from './team_members/reducers'
import qaReducer from './qtr_accountabilities/reducers'
import reviewReducer from './review/reducers'

import clearWrapper from './core/clearWrapper'


const submissionReducerInternal = combineReducers({
    core: coreReducer,
    applications: applicationReducer,
    team_members: teamMembersReducer,
    courses: courseReducer,
    scoreboard: scoreboardReducer,
    qtr_accountabilities: qaReducer,
    review: reviewReducer
})

export const submissionReducer = clearWrapper(submissionReducerInternal, (state, action) => {
    return submissionReducerInternal({core: state.core}, action)
})
