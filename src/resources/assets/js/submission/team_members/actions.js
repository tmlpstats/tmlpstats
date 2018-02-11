import _ from 'lodash'
import { actions as formActions } from 'react-redux-form'

import { getMessages } from '../../reusable/ajax_utils'
import { objectAssign } from '../../reusable/ponyfill'
import Api from '../../api'
import { markStale } from '../review/actions'

import { teamMembersData, weeklyReportingData, weeklyReportingSave, messages } from './data'
import { TEAM_MEMBERS_COLLECTION_FORM_KEY, TEAM_MEMBER_FORM_KEY } from './reducers'
import { determineExitChoice, exitChoiceMerges } from './exit_choice'

const weeklySaveState = weeklyReportingSave.actionCreator()

export function conditionalLoadTeamMembers(centerId, reportingDate) {
    return (dispatch, getState) => {
        if (getState().submission.team_members.teamMembers.loadState.state == 'new') {
            return dispatch(loadTeamMembers(centerId, reportingDate))
        }
    }
}

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

export function initializeTeamMembers(data) {
    return (dispatch, getState) => {
        const state = getState()
        _.forEach(data, (teamMember) => {
            teamMember.exitChoice = determineExitChoice(teamMember, state)
            if(typeof(teamMember.tdo) === 'boolean'){
                teamMember.tdo = teamMember.tdo ? 1 : 0
            }
        })
        // Re-format the collection as a key-ordered collection
        data = _.keyBy(data, 'id')

        dispatch(formActions.load(TEAM_MEMBERS_COLLECTION_FORM_KEY, data))
    }
}

export function chooseTeamMember(data) {
    return formActions.load(TEAM_MEMBER_FORM_KEY, data)
}

export function weeklyReportingUpdated(teamMemberId) {
    return weeklyReportingData.mark(teamMemberId)
}

export function markWeeklyReportingFromModel(model) {
    const bits = model.split('.')
    const target = bits.length - 2 // the model looks like path.<teamMemberid>.tdo so we can get it from 2nd to last
    return weeklyReportingUpdated(bits[target])
}

const WEEKLY_REPORTING_KEYS = ['gitw', 'tdo', 'travel', 'room', 'rppCap', 'rppCpc', 'rppLf']

export function weeklyReportingSubmit(center, reportingDate, tracking, rawData) {
    var updates = []
    for (var id in tracking.changed) {
        const input = rawData[id]
        const update = {id}
        // For each of the WEEKLY_REPORTING_KEYS on the teamMember, update if assigned.
        for (const k of WEEKLY_REPORTING_KEYS) {
            if (input[k] !== undefined && input[k] !== null) {
                update[k] = input[k]
            }
        }
        updates.push(update)
    }

    return (dispatch) => {
        dispatch(weeklyReportingData.beginWork())
        dispatch(weeklySaveState('loading'))

        const success = (data) => {
            dispatch(markStale())
            dispatch(weeklySaveState('loaded'))
            if (data && data.messages) {
                dispatch(messages.replaceMany(data.messages))
            }
            setTimeout(() => {
                dispatch(weeklyReportingData.endWork())
                dispatch(weeklySaveState('new'))
                // make an inconsequential change to each team member to trigger re-rendering
                updates.forEach((update) => {
                    const tm = rawData[update.id]
                    const newCounter = (tm._ctr || 0) + 1
                    dispatch(formActions.change(`${TEAM_MEMBERS_COLLECTION_FORM_KEY}.${update.id}._ctr`, newCounter))
                })
            }, 3000)
            return data
        }
        const fail = (err) => {
            dispatch(weeklySaveState({error: err.error || err}))
        }

        return Api.TeamMember.bulkStashWeeklyReporting({center, reportingDate, updates}).then(success, fail)
    }
}

export function selectChangeAction(model, value) {
    return (dispatch) => {
        const newValue = (value === '') ? null : value
        dispatch(formActions.change(model, newValue))

        let bits = model.split('.')
        bits.reverse() // the model looks like path.<teamMemberid>.tdo so if we reverse it, we get the right answer
        dispatch(weeklyReportingUpdated(bits[1]))
    }
}

export function setExitChoice(exitChoice) {
    const merges = exitChoiceMerges(exitChoice)
    return formActions.merge(TEAM_MEMBER_FORM_KEY, merges)
}

export function stashTeamMember(center, reportingDate, data) {
    return teamMembersData.runNetworkAction('save', {center, reportingDate, data}, {
        successHandler(result, { dispatch }) {
            dispatch(markStale())
            // The request failed before creating an id (parser error)
            if (data.action == 'delete') {
                // Force reloading team members rather than deleting in our state machine.
                setTimeout(() => dispatch(teamMembersData.loadState('new')), 50)
                return
            }
            if (!result.storedId) {
                dispatch(teamMembersData.saveState({error: 'Validation Failed', messages: result.messages}))
                setTimeout(() => { dispatch(teamMembersData.saveState('new')) }, 3000)
                dispatch(messages.replace('create', result.messages))
                return
            }

            const newData = objectAssign({}, data, {id: result.storedId, quarterNumber: result.meta.quarterNumber, meta: result.meta})
            dispatch(messages.replace(newData.id, getMessages(result)))
            dispatch(teamMembersData.replaceItem(newData.id, newData))

            // If this is a new entry, clear out any messages
            if (!data.id) {
                dispatch(messages.replace('create', []))
            }
        },

        failHandler(err, { dispatch }) {
            // If this is a parser error, we won't have an ID yet, use 'create'
            const id = data.id ? data.id : 'create'
            dispatch(messages.replace(id, getMessages(err)))
        }
    })
}

export function rppChangeAction(model, value) {
    return (dispatch) => {
        dispatch(markWeeklyReportingFromModel(model))
        if (value === '') {
            value = null
        } else if (value !== null) {
            value = parseInt(value)
        }
        dispatch(formActions.change(model, value))
    }
}
