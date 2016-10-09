///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

import { getMessages } from '../../reusable/ajax_utils'
import Api from '../../api'

import { appsCollection, applicationsLoad, saveAppLoad, messages } from './data'

export const loadState = applicationsLoad.actionCreator()
export const saveAppState = saveAppLoad.actionCreator()

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
        }).catch(() => {
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

        dispatch(saveAppState('loading'))
        return Api.Application.stash({
            center, reportingDate, data
        }).then((result) => {
            reset()
            return result // Because it's a Promise is you have to return results to continue the chain.
        }).catch((err) => {
            dispatch(messages.replace(data.id, getMessages(err)))
            reset()
        })
    }
}

export function setAppStatus(status) {
    return formActions.change('submission.applications.currentApp.appStatus', status)
}
