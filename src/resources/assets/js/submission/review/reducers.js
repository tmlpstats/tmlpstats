import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'
import { reportSubmitting, displayFlow } from './data'

export const REVIEW_SUBMIT_FORM_KEY = 'submission.review.submitData'

function messagesReducer(state=null, action) {
    if (action.type == 'review/setMessages') {
        state = action.payload
    }
    return state
}

function submitResultReducer(state=null, action) {
    if (action.type == 'review/setSubmitResults') {
        state = action.payload
    }
    return state
}

export default combineReducers({
    oldMessages: messagesReducer,
    submitData: modelReducer(REVIEW_SUBMIT_FORM_KEY),
    submitDataForm: formReducer(REVIEW_SUBMIT_FORM_KEY),
    submitResults: submitResultReducer,
    reportSubmitting: reportSubmitting.reducer(),
    displayFlow: displayFlow.reducer(),
})
