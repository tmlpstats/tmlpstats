///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

import { getMessages } from '../../reusable/ajax_utils'
import { objectAssign } from '../../reusable/ponyfill'
import { scrollIntoView } from '../../reusable/ui_basic'
import Api from '../../api'

import { appsCollection, applicationsLoad, saveAppLoad, messages } from './data'

export const loadState = applicationsLoad.actionCreator()
export const saveAppState = saveAppLoad.actionCreator()

export function conditionalLoadApplications(centerId, reportingDate) {
    return (dispatch, getState) => {
        if (getState().submission.applications.loading.state == 'new') {
            return dispatch(loadApplications(centerId, reportingDate))
        }
    }
}

export function loadApplications(centerId, reportingDate) {
    return (dispatch) => {
        dispatch(loadState('loading'))
        return Api.Application.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true,
        }).then((data) => {
            dispatch(initializeApplications(data))
            return data
        }).catch((err) => {
            console.log(err)
            dispatch(loadState('failed'))
        })
    }
}

export function initializeApplications(data) {
    // This is a redux thunk which dispatches two different actions on result
    return (dispatch) => {
        dispatch(appsCollection.replaceCollection(data))
        dispatch(loadState('loaded'))
    }
}

export function chooseApplication(appId, app) {
    return formActions.change('submission.applications.currentApp', app)
}

export function saveApplication(center, reportingDate, data) {
    return (dispatch) => {
        const reset = () => dispatch(saveAppState('new'))

        if (data.committedTeamMember == '') {
            data = objectAssign({}, data, {committedTeamMember: undefined})
        }

        dispatch(saveAppState('loading'))
        return Api.Application.stash({
            center, reportingDate, data
        }).then((result) => {
            // Failed during validation
            if (!result.storedId) {
                dispatch(messages.replace('create', result.messages))
                reset()
                return result
            }

            let newData = objectAssign({}, data, {id: result.storedId})
            dispatch(appsCollection.replaceItem(newData.id, newData))
            dispatch(messages.replace(result.storedId, result.messages))

            // We successfully saved, reset any existing parser messages
            if (!data.id) {
                dispatch(messages.replace('create', []))
            }
            reset()
            return result // Because it's a Promise is you have to return results to continue the chain.
        }).catch((err) => {
            // If this is a parser error, we won't have an ID yet, use 'create'
            const id = data.id ? data.id : 'create'
            dispatch(messages.replace(id, getMessages(err)))

            reset()
            scrollIntoView('react-routed-flow')
        })
    }
}

export function setAppStatus(status) {
    return formActions.change('submission.applications.currentApp.appStatus', status)
}
