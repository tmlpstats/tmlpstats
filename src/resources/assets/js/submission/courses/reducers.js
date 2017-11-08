import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { coursesCollection, coursesLoad, saveCourseLoad, messages, BLANK_COURSE } from './data'

const COURSES_BASE_KEY = 'submission.courses'
export const COURSES_FORM_KEY = COURSES_BASE_KEY + '.currentCourse'
export const COURSES_QSTART_EDITABLE = COURSES_BASE_KEY + '.qstartEditable'


export const courseReducer = combineReducers({
    loading: coursesLoad.reducer(),
    courses: coursesCollection.reducer(),
    currentCourse: modelReducer(COURSES_FORM_KEY, BLANK_COURSE),
    forms: formReducer(COURSES_BASE_KEY, {currentCourse: BLANK_COURSE}),
    qstartEditable: modelReducer(COURSES_QSTART_EDITABLE),
    saveCourse: saveCourseLoad.reducer(),
    messages: messages.reducer(),
})
