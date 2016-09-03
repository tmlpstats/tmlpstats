import { combineReducers } from 'redux'
import { regionsData, centersData, scoreboardLockData } from './data'

const regionsReducer = combineReducers({
    regions: regionsData.reducer(),
    centers: centersData.reducer(),
    scoreboardLock: scoreboardLockData.reducer()
})

export default regionsReducer
