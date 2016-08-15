import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { coursesCollection, coursesLoad, saveCourseLoad, messages } from './data'

export const COURSES_FORM_KEY = 'submission.courses.currentCourse'


export const courseReducer = combineReducers({
    loading: coursesLoad.reducer(),
    courses: coursesCollection.reducer(),
    currentCourse: modelReducer(COURSES_FORM_KEY, []),
    currentCourseForm: formReducer(COURSES_FORM_KEY, []),
    saveCourse: saveCourseLoad.reducer(),
    messages: messages.reducer(),
})
