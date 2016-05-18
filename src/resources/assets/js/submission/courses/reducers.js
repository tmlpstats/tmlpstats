import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { INITIALIZE_COURSES } from './actions'

function loadedReducer(state = false, action) {
    switch (action.type) {
    case INITIALIZE_COURSES:
        return true
    }
    return state
}

export const COURSES_FORM_KEY = 'submission.course.courses'

export const courseReducer = combineReducers({
    loaded: loadedReducer,
    courses: modelReducer(COURSES_FORM_KEY, []),
    coursesForm: formReducer(COURSES_FORM_KEY, [])
})
