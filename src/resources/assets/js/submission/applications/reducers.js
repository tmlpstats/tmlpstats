import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { appsCollection, applicationsLoad, saveAppLoad, messages } from './data'

const APPLICATIONS_BASE_KEY = 'submission.applications'
export const APPLICATIONS_FORM_KEY = APPLICATIONS_BASE_KEY + '.currentApp'


export const applicationReducer = combineReducers({
    loading: applicationsLoad.reducer(),
    applications: appsCollection.reducer(),
    currentApp: modelReducer(APPLICATIONS_FORM_KEY),
    forms: formReducer(APPLICATIONS_BASE_KEY),
    saveApp: saveAppLoad.reducer(),
    messages: messages.reducer(),
})
