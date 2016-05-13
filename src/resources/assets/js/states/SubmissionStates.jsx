import { combineReducers } from 'redux'


function applicationReducer(state, action) {
    if (!state) {
        state = {loaded: false}
    }
    return state
}

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
