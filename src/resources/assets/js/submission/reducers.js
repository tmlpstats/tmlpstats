import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'

function scoreboardReducer(state, action) {
    if (!state) {
        state = {loaded: false}
    }
    return state
}

function coreReducer(state={}, action) {
    if (action) {
        switch (action.type) {
        case 'submission.setReportingDate':
            return Object.assign({}, state, {reportingDate: action.payload})
        }
    }
    return state
}


const submissionReducerInternal = combineReducers({
    core: coreReducer,
    applications: applicationReducer,
    course: courseReducer,
    scoreboard: scoreboardReducer
})

// This is a diabolically simple reducer wrapper to help clear submission data
//
// The actual work is handled by the combineReducers reducer, but when the specific
// action 'submission.setReportingDate' comes through, we revert all submission state
// to default except for the "core" state
export function submissionReducer(state, action) {
    if (action.type == 'submission.setReportingDate') {
        return submissionReducerInternal({core: state.core}, action)
    }
    return submissionReducerInternal(state, action)
}
