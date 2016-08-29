///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'
import { bestErrorValue } from '../../reusable/ajax_utils'
import { appsCollection, applicationsLoad, saveAppLoad, messages } from './data'

export const loadState = applicationsLoad.actionCreator()
export const saveAppState = saveAppLoad.actionCreator()

export function loadApplications(centerId, reportingDate) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        return Api.Application.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true,
        }).done((data) => {
            dispatch(initializeApplications(data))
        }).fail(() => {
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
    return (dispatch, _, { Api }) => {
        const reset = () => dispatch(saveAppState('new'))

        dispatch(saveAppState('loading'))
        return Api.Application.stash({
            center, reportingDate, data
        }).done(() => {
            reset()
        }).fail((xhr, textStatus) => {
            const message = bestErrorValue(xhr, textStatus)
            dispatch(saveAppState('new'))
            dispatch(messages.replace(data.id, [message]))
        })
    }
}

export function setAppStatus(status) {
    return formActions.change('submission.applications.currentApp.appStatus', status)
}
