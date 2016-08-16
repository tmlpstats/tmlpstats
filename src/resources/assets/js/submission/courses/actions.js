///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'
import { bestErrorValue } from '../../reusable/ajax_utils'
import { coursesCollection, coursesLoad, saveCourseLoad, messages } from './data'

export const loadState = coursesLoad.actionCreator()
export const saveCourseState = saveCourseLoad.actionCreator()

export function loadCourses(centerId, reportingDate) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        return Api.Course.allForCenter({
            center: centerId,
            includeInProgress: true,
            reportingDate: reportingDate
        }).done((data) => {
            dispatch(initializeCourses(data))
        }).fail(() => {
            dispatch(loadState('failed'))
        })
    }
}

export function initializeCourses(data) {
    // This is a redux thunk which dispatches two different actions on result
    return (dispatch) => {
        dispatch(coursesCollection.replaceCollection(data))
        dispatch(loadState('loaded'))
    }
}

export function chooseCourse(courseId, course) {
    return formActions.change('submission.courses.currentCourse', course)
}

export function saveCourse(center, reportingDate, data) {
    return (dispatch, _, { Api }) => {
        const reset = () => dispatch(saveCourseState('new'))

        dispatch(saveCourseState('loading'))
        return Api.Course.stash({
            center, reportingDate, data
        }).done(() => {
            reset()
        }).fail((xhr, textStatus) => {
            const message = bestErrorValue(xhr, textStatus)
            dispatch(saveCourseState('new'))
            dispatch(messages.replace(data.id, [message]))
        })
    }
}
