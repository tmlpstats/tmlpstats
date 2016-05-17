///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

export const INITIALIZE_APPLICATIONS = 'applications.initialize'


export function initializeApplications(data) {
    // This is a redux thunk which dispatches two different actions on result
    return (dispatch) => {
        dispatch({type: INITIALIZE_APPLICATIONS})
        dispatch(formActions.change('submission.application.applications', data))
    }
}
