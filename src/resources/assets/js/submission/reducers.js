import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'
import { courseReducer } from './courses/reducers'

function scoreboardReducer(state, action) {
    if (!state) {
        state = {loaded: false}
    }
    return state
}


export let submissionReducer = combineReducers({
    application: applicationReducer,
    course: courseReducer,
    scoreboard: scoreboardReducer
})
