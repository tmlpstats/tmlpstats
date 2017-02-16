///// ACTION CREATORS
import { actions as formActions } from 'react-redux-form'

import { getMessages } from '../../reusable/ajax_utils'
import { objectAssign } from '../../reusable/ponyfill'
import { scrollIntoView } from '../../reusable/ui_basic'
import Api from '../../api'
import { coursesCollection, coursesLoad, saveCourseLoad, messages } from './data'

export const loadState = coursesLoad.actionCreator()
export const saveCourseState = saveCourseLoad.actionCreator()

export function loadCourses(centerId, reportingDate) {
    return (dispatch) => {
        dispatch(loadState('loading'))

        const successHandler = (data) => {
            dispatch(initializeCourses(data))
            return data
        }
        const failHandler = (err) => {
            dispatch(loadState({error: err.error || err}))
        }

        return Api.Course.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true,
        }).then(successHandler, failHandler)
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
    return (dispatch) => {
        const reset = () => dispatch(saveCourseState('new'))

        dispatch(saveCourseState('loading'))
        return Api.Course.stash({
            center, reportingDate, data
        }).then((result) => {
            // Failed during validation
            if (!result.storedId) {
                dispatch(messages.replace('create', result.messages))
                reset()
                return result
            }

            data = objectAssign({}, data, {id: result.storedId, meta: result.meta})
            dispatch(coursesCollection.replaceItem(data))
            dispatch(messages.replace(data.id, result.messages))

            // We successfully saved, reset any existing parser messages
            if (!data.id) {
                dispatch(messages.replace('create', []))
            }
            reset()
            return result
        }).catch((err) => {
            // If this is a parser error, we won't have an ID yet, use 'create'
            const id = data.id ? data.id : 'create'
            dispatch(messages.replace(id, getMessages(err)))

            reset()
            scrollIntoView('submission-flow')
        })
    }
}
