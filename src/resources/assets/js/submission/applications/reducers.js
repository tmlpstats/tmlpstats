import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { loadingMultiState } from '../../reusable/reducers'
import { APPLICATIONS_LOAD_STATE, APPLICATIONS_SAVE_APP_STATE } from './actions'
import { appsCollection } from './data'

export const APPLICATIONS_FORM_KEY = 'submission.application.currentApp'


export const applicationReducer = combineReducers({
    loading: loadingMultiState(APPLICATIONS_LOAD_STATE),
    applications: appsCollection.reducer(),
    currentApp: modelReducer(APPLICATIONS_FORM_KEY, []),
    currentAppForm: formReducer(APPLICATIONS_FORM_KEY, []),
    saveApp: loadingMultiState(APPLICATIONS_SAVE_APP_STATE)
})
