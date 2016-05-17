import { combineReducers } from 'redux'
import { applicationReducer } from './applications/reducers'

function scoreboardReducer(state, action) {
    if (!state) {
        state = {loaded: false}
    }
    return state
}


export let submissionReducer = combineReducers({
    application: applicationReducer,
    scoreboard: scoreboardReducer
})
