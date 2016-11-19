import { formReducer, modelReducer } from 'react-redux-form'
import { combineReducers } from 'redux'
import { regionsData, centersData, scoreboardLockData } from './data'

const regionsReducer = combineReducers({
    regions: regionsData.reducer(),
    centers: centersData.reducer(),
    scoreboardLock: scoreboardLockData.reducer(),
    quarterDates: modelReducer('admin.regions.quarterDates'),
    quarterDatesForm: formReducer('admin.regions.quarterDates')
})

export default regionsReducer
