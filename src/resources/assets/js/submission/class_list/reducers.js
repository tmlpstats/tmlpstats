import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { objectAssign } from '../../reusable/ponyfill'
import { classListLoad, teamMembersCollection } from './data'

export const TEAM_MEMBERS_COLLECTION_FORM_KEY = 'submission.class_list.teamMembers.collection'
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

const classListReducer = combineReducers({
    loading: classListLoad.reducer(),
    currentMember: modelReducer(TEAM_MEMBER_FORM_KEY),
    teamMembers: teamMembersCollection.reducer(modelReducer(TEAM_MEMBERS_COLLECTION_FORM_KEY)),
    prefs: prefsReducer
})

export default classListReducer
