import { combineReducers } from 'redux'

import { objectAssign } from '../../reusable/ponyfill'

import { coreInit } from './data'
import clearWrapper from './clearWrapper'

function reportingDate(state='', action) {
    switch (action.type) {
    case 'submission.setReportingDate':
        return action.payload
    }
    return state
}

export function lookups(state={}, action) {
    switch (action.type) {
    case 'core/setSubmissionLookups':
        return objectAssign({}, state, action.payload)
    }
    return state
}

const coreReducerReal = combineReducers({
    coreInit: coreInit.reducer(),
    lookups: lookups,
    reportingDate: reportingDate
})

const coreReducer = clearWrapper(coreReducerReal, (state, action) => {
    return coreReducerReal({reportingDate: state.reportingDate}, action)
})

export default coreReducer
