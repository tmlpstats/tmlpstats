import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { APPLICATIONS_LOAD_STATE } from './actions'
import { loadingMultiState } from '../../reusable/reducers'

export const APPLICATIONS_FORM_KEY = 'submission.application.applications'

export const applicationReducer = combineReducers({
    loading: loadingMultiState(APPLICATIONS_LOAD_STATE),
    applications: modelReducer(APPLICATIONS_FORM_KEY, []),
    applicationsForm: formReducer(APPLICATIONS_FORM_KEY, [])
})
