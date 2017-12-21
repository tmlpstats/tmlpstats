import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { weeklyReportingSave, weeklyReportingData, teamMembersData, messages } from './data'

export const TEAM_MEMBERS_COLLECTION_FORM_KEY = 'submission.team_members.teamMembers.data'
export const TEAM_MEMBER_FORM_KEY = 'submission.team_members.currentMember'



const teamMembersReducer = combineReducers({
    forms: formReducer('submission.team_members'),
    currentMember: modelReducer(TEAM_MEMBER_FORM_KEY),
    teamMembers: teamMembersData.reducer({formReducer: true}),
    weeklyReporting: weeklyReportingData.reducer(),
    weeklySave: weeklyReportingSave.reducer(),
    messages: messages.reducer()
})

export default teamMembersReducer
