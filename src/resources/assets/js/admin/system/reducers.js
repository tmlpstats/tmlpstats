import { combineReducers } from 'redux'
import { formReducer, modelReducer } from 'react-redux-form'

export default combineReducers({
    forms: formReducer('admin.system', {currentMessage: {}}),
    currentMessage: modelReducer('admin.system.currentMessage', {})
})
