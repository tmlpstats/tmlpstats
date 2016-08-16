import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { appsCollection, applicationsLoad, saveAppLoad, messages } from './data'

export const APPLICATIONS_FORM_KEY = 'submission.applications.currentApp'


export const applicationReducer = combineReducers({
    loading: applicationsLoad.reducer(),
    applications: appsCollection.reducer(),
    currentApp: modelReducer(APPLICATIONS_FORM_KEY, []),
    currentAppForm: formReducer(APPLICATIONS_FORM_KEY, []),
    saveApp: saveAppLoad.reducer(),
    messages: messages.reducer(),
})
