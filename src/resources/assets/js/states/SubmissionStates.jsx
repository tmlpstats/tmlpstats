import { combineReducers } from 'redux'
import { modelReducer, formReducer, actions as formActions } from 'react-redux-form'

// TMLP applications
const INITIALIZE_APPLICATIONS = 'applications.initialize'


///// ACTION CREATORS

export function initializeApplications(data) {
    // This is a redux thunk which dispatches two different actions on result
    return (dispatch) => {
        dispatch({type: INITIALIZE_APPLICATIONS})
        dispatch(formActions.change('submission.application.applications', data))
    }
}

///// REDUCERS

function loadedReducer(state = false, action) {
    switch (action.type) {
    case INITIALIZE_APPLICATIONS:
        return true
    }
    return state
}

export const APPLICATIONS_FORM_KEY = 'submission.application.applications'

const applicationReducer = combineReducers({
    loaded: loadedReducer,
    applications: modelReducer(APPLICATIONS_FORM_KEY, []),
    applicationsForm: formReducer(APPLICATIONS_FORM_KEY, [])
})

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
