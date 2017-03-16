import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { objectAssign } from '../../reusable/ponyfill'
import { weeklyReportingSave, weeklyReportingData, teamMembersData, messages } from './data'

export const TEAM_MEMBERS_COLLECTION_FORM_KEY = 'submission.team_members.teamMembers.data'
export const TEAM_MEMBER_FORM_KEY = 'submission.team_members.currentMember'

const DEFAULT_PREFS = {
    showTravelRooming: false,
    requireTravelRoomingPromise: false
}

function prefsReducer(state=DEFAULT_PREFS, action) {
    switch (action.type) {
    case 'team_members/replace_prefs':
        return objectAssign({}, DEFAULT_PREFS, action.payload)
    }
    return state
}

const teamMembersReducer = combineReducers({
    forms: formReducer('submission.team_members'),
    currentMember: modelReducer(TEAM_MEMBER_FORM_KEY),
    teamMembers: teamMembersData.reducer({formReducer: true}),
    prefs: prefsReducer,
    weeklyReporting: weeklyReportingData.reducer(),
    weeklySave: weeklyReportingSave.reducer(),
    messages: messages.reducer()
})

export default teamMembersReducer
