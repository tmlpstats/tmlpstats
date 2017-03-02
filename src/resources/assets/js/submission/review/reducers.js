import { combineReducers } from 'redux'
import { modelReducer } from 'react-redux-form'

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

function preSubmitReducer(state=false, action) {
    if (action.type == 'review/setPreSubmitModal') {
        return action.payload
    }

    return state
}

function postSubmitReducer(state=false, action) {

    if (action.type == 'review/setPostSubmitModal') {
        return action.payload
    }

    return state
}

export default combineReducers({
    oldMessages: messagesReducer,
    submitData: modelReducer(REVIEW_SUBMIT_FORM_KEY),
    submitResults: submitResultReducer,
    showPreSubmitModal: preSubmitReducer,
    showPostSubmitModal: postSubmitReducer,
})
