///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'
import { appsCollection } from './data'

export const APPLICATIONS_LOAD_STATE = 'applications/initialLoadState'
export const APPLICATIONS_SAVE_APP_STATE = 'applications/saveAppState'

export function loadState(state) {
    return {type: APPLICATIONS_LOAD_STATE, payload: state}
}

export function saveAppState(state) {
    return {type: APPLICATIONS_SAVE_APP_STATE, payload: state}
}

export function loadApplications(centerId) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        return Api.Application.allForCenter({
            center: centerId
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
    // I really don't like this pattern. Must cogitate on a better one.
    return (dispatch, getState) => {
        if (!app) {
            app = getState().submission.application.applications.collection[appId]
        }
        dispatch(formActions.change('submission.application.currentApp', app))
    }
}

export function saveApplication(application, reportingDate, data) {
    return (dispatch, _, { Api }) => {
        dispatch(saveAppState('loading'))
        return Api.Application.setWeekData({
            application, reportingDate, data
        }).done(() => {
            dispatch(saveAppState('new'))
        }).fail(() => {
            dispatch(saveAppState('failed'))
        })
    }
}
