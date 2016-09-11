import { combineReducers } from 'redux'
import { modelReducer } from 'react-redux-form'

import { objectAssign } from '../../reusable/ponyfill'
import { weeklyReportingSave, weeklyReportingData, teamMembersData } from './data'

export const TEAM_MEMBERS_COLLECTION_FORM_KEY = 'submission.class_list.teamMembers.data.collection'
export const TEAM_MEMBER_FORM_KEY = 'submission.class_list.currentMember'

const DEFAULT_PREFS = {
    showTravelRooming: false,
    requireTravelRoomingPromise: false
}

function prefsReducer(state=DEFAULT_PREFS, action) {
    switch (action.type) {
    case 'class_list/replace_prefs':
        return objectAssign({}, DEFAULT_PREFS, action.payload)
    }
    return state
}

function resortCheck(a, b, action) {
    if (action.type == 'rrf/change') {
        return action.silent || false // if this was a silent action, we want to rebuild sort
    }
    return false
}

const classListReducer = combineReducers({
    currentMember: modelReducer(TEAM_MEMBER_FORM_KEY),
    teamMembers: teamMembersData.reducer({
        collection_reducer: modelReducer(TEAM_MEMBERS_COLLECTION_FORM_KEY),
        check_resort: resortCheck
    }),
    prefs: prefsReducer,
    weeklyReporting: weeklyReportingData.reducer(),
    weeklySave: weeklyReportingSave.reducer()
})

export default classListReducer
