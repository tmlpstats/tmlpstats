import { combineReducers } from 'redux'

function messagesReducer(state=null, action) {
    if (action.type == 'review/setMessages') {
        state = action.payload
    }
    return state
}

export default combineReducers({
    oldMessages: messagesReducer
})
