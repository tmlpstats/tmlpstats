import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'
import { scoreboardReducer } from './scoreboard/reducers'
import coreReducer from './core/reducers'
import teamMembersReducer from './team_members/reducers'
import nqaReducer from './next_qtr_accountabilities/reducers'
import reviewReducer from './review/reducers'
import programLeadersReducer from './program_leaders/reducers'

import clearWrapper from './core/clearWrapper'


const submissionReducerInternal = combineReducers({
    core: coreReducer,
    applications: applicationReducer,
    team_members: teamMembersReducer,
    courses: courseReducer,
    scoreboard: scoreboardReducer,
    next_qtr_accountabilities: nqaReducer,
    review: reviewReducer,
    program_leaders: programLeadersReducer
})

export const submissionReducer = clearWrapper(submissionReducerInternal, (state, action) => {
    return submissionReducerInternal({core: state.core}, action)
})
