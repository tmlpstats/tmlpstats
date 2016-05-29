///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

export const APPLICATIONS_LOAD_STATE = 'applications/loadState'

export function loadState(state) {
    return {type: APPLICATIONS_LOAD_STATE, payload: state}
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
        dispatch(formActions.change('submission.application.applications', data))
        dispatch(loadState('loaded'))
    }
}

