import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { INITIALIZE_APPLICATIONS } from './actions'

function loadedReducer(state = false, action) {
    switch (action.type) {
    case INITIALIZE_APPLICATIONS:
        return true
    }
    return state
}

export const APPLICATIONS_FORM_KEY = 'submission.application.applications'

export const applicationReducer = combineReducers({
    loaded: loadedReducer,
    applications: modelReducer(APPLICATIONS_FORM_KEY, []),
    applicationsForm: formReducer(APPLICATIONS_FORM_KEY, [])
})
