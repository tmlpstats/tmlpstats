import { actions as formActions } from 'react-redux-form'

import { bestErrorValue } from '../../reusable/ajax_utils'
import { classListLoad, teamMembersCollection } from './data'
import { TEAM_MEMBERS_COLLECTION_FORM_KEY, TEAM_MEMBER_FORM_KEY } from './reducers'

export const loadState = classListLoad.actionCreator()

export function loadClassList(centerId, reportingDate) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        return Api.TeamMember.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true
        }).done((data) => {
            dispatch(initializeClassList(data))
        }).fail((jqXHR, textStatus) => {
            dispatch(loadState({error: bestErrorValue(jqXHR, textStatus)}))
        })
    }
}

function initializeClassList(data) {
    return (dispatch) => {
        // Re-format the collection as a key-ordered collection
        data = teamMembersCollection.ensureCollection(data)
        dispatch(formActions.load(TEAM_MEMBERS_COLLECTION_FORM_KEY, data))
        dispatch(loadState('loaded'))
    }
}

export function chooseTeamMember(data) {
    console.log('load', TEAM_MEMBER_FORM_KEY, data)
    return formActions.load(TEAM_MEMBER_FORM_KEY, data)
}
