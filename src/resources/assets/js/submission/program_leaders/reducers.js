import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'
import { programLeadersData, messages } from './data'

export const REVEIW_LEADER_FORM_KEY = 'submission.program_leaders.currentLeader'

export default combineReducers({
    forms: formReducer('submission.program_leaders'),
    messages: messages.reducer(),
    currentLeader: modelReducer(REVEIW_LEADER_FORM_KEY),
    programLeaders: programLeadersData.reducer(),
})
