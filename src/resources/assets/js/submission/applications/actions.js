///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'
import { bestErrorValue } from '../../reusable/ajax_utils'
import { appsCollection, applicationsLoad, saveAppLoad } from './data'

export const loadState = applicationsLoad.actionCreator()
export const saveAppState = saveAppLoad.actionCreator()

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
    return formActions.change('submission.application.currentApp', app)
}

export function saveApplication(application, reportingDate, data) {
    return (dispatch, _, { Api }) => {
        const reset = () => dispatch(saveAppState('new'))

        dispatch(saveAppState('loading'))
        return Api.Application.setWeekData({
            application, reportingDate, data
        }).done(() => {
            reset()
        }).fail((xhr, textStatus) => {
            dispatch(saveAppState({state: 'failed', error: bestErrorValue(xhr, textStatus)}))
            setTimeout(reset, 3000)
        })
    }
}
