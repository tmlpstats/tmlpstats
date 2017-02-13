import _ from 'lodash'
import { actions as formActions } from 'react-redux-form'

import { getMessages } from '../../reusable/ajax_utils'
import { objectAssign } from '../../reusable/ponyfill'
import Api from '../../api'

import { teamMembersData, weeklyReportingData, weeklyReportingSave, messages } from './data'
import { TEAM_MEMBERS_COLLECTION_FORM_KEY, TEAM_MEMBER_FORM_KEY } from './reducers'
import { determineExitChoice, exitChoiceMerges } from './exit_choice'

const weeklySaveState = weeklyReportingSave.actionCreator()

export function loadTeamMembers(centerId, reportingDate) {
    const params = {
        center: centerId,
        reportingDate: reportingDate,
        includeInProgress: true
    }
    return teamMembersData.load(params, {
        successHandler: initializeTeamMembers
    })
}

function initializeTeamMembers(data) {
    return (dispatch, getState) => {
        const state = getState()
        _.forEach(data, (teamMember) => {
            teamMember.exitChoice = determineExitChoice(teamMember, state)
        })
        // Re-format the collection as a key-ordered collection
        data = teamMembersData.ensureCollection(data)

        dispatch(formActions.load(TEAM_MEMBERS_COLLECTION_FORM_KEY, data))
        dispatch(teamMembersData.loadState('loaded'))
    }
}

export function chooseTeamMember(data) {
    return formActions.load(TEAM_MEMBER_FORM_KEY, data)
}

export function weeklyReportingUpdated(teamMemberId) {
    return weeklyReportingData.mark(teamMemberId)
}

export function weeklyReportingSubmit(center, reportingDate, tracking, rawData) {
    var updates = []
    for (var id in tracking.changed) {
        let {gitw, tdo} = rawData[id]
        updates.push({id, gitw, tdo})
    }

    return (dispatch) => {
        dispatch(weeklyReportingData.beginWork())
        dispatch(weeklySaveState('loading'))

        const success = (data) => {
            dispatch(weeklySaveState('loaded'))
            if (data && data.messages) {
                dispatch(messages.replaceMany(data.messages))
            }
            setTimeout(() => {
                dispatch(weeklyReportingData.endWork())
                dispatch(weeklySaveState('new'))
            }, 3000)
            return data
        }
        const fail = (err) => {
            dispatch(weeklySaveState({error: err.error || err}))
        }

        return Api.TeamMember.bulkStashWeeklyReporting({center, reportingDate, updates}).then(success, fail)
    }
}

export function setExitChoice(exitChoice) {
    const merges = exitChoiceMerges(exitChoice)
    return formActions.merge(TEAM_MEMBER_FORM_KEY, merges)
}

export function stashTeamMember(center, reportingDate, data) {
    return teamMembersData.runNetworkAction('save', {center, reportingDate, data}, {
        successHandler(result, { dispatch }) {
            // The request failed before creating an id (parser error)
            if (!result.storedId) {
                dispatch(teamMembersData.saveState({error: 'Validation Failed', messages: result.messages}))
                setTimeout(() => { dispatch(teamMembersData.saveState('new')) }, 3000)
                dispatch(messages.replace('create', result.messages))
                return
            }

            const newData = objectAssign({}, data, {id: result.storedId, meta: result.meta})
            dispatch(messages.replace(newData.id, getMessages(result)))
            dispatch(teamMembersData.replaceItem(newData))

            // If this is a new entry, clear out any messages
            if (!data.id) {
                dispatch(messages.replace('create', []))
            }
        }
    })
}
