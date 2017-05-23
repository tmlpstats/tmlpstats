import { formReducer, modelReducer } from 'react-redux-form'
import { combineReducers } from 'redux'
import { regionsData, centersData, scoreboardLockData, extraData } from './data'

const regionsReducer = combineReducers({
    form: formReducer('admin.regions'),
    regions: regionsData.reducer(),
    centers: centersData.reducer(),
    scoreboardLock: scoreboardLockData.reducer(),
    extra: extraData.reducer(),
    quarterDates: modelReducer('admin.regions.quarterDates'),
})

export default regionsReducer
