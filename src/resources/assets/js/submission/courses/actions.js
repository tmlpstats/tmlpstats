///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

export const INITIALIZE_COURSES = 'courses.initialize'


export function initializeCourses(data) {
    // This is a redux thunk which dispatches two different actions on result
    return (dispatch) => {
        dispatch({type: INITIALIZE_COURSES})
        dispatch(formActions.change('submission.course.courses', data))
    }
}
