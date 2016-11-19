import { delayDispatch } from '../../reusable/dispatch'
import { objectAssign } from '../../reusable/ponyfill'
import { normalize } from 'normalizr'
import { regionsData, centersData, scoreboardLockData, regionSchema } from './data'

export function loadRegionsData(region) {
    return regionsData.load({region}, {
        successHandler: (data, {dispatch}) => {
            dispatch(initializeRegionsData(region, data))
        }
    })
}

function initializeRegionsData(regionAbbr, data) {
    return (dispatch) => {
        const n = normalize(data, regionSchema)
        const region = n.entities.regions[n.result]
        region.regionQuarters = n.entities.regionQuarters
        dispatch(regionsData.replaceItem(regionAbbr, region))
        dispatch(centersData.replaceItems(n.entities.centers))
        dispatch(regionsData.loadState('loaded'))
    }
}

export function loadScoreboardLockData(center, quarter) {
    // scoreboardLockData.load is already a thunk, we don't need to re-thunkify, so long as it's the only action we're doing
    return scoreboardLockData.load({center, quarter}, {
        successHandler: (data) => {
            return initializeScoreboardLockData(center, quarter, data)
        }
    })
}

function initializeScoreboardLockData(centerId, quarterId, data) {
    return (dispatch) => {
        data = objectAssign({}, data, {centerId, quarterId, applyCenter: centerId})
        // TODO decide on center / quarter info being part of the key
        dispatch(scoreboardLockData.replaceCollection(data))
        dispatch(scoreboardLockData.loadState('loaded'))
    }
}

export function saveScoreboardLocks(center, quarter, data, clear) {
    return (dispatch, _, { Api }) => {
        dispatch(scoreboardLockData.saveState('loading'))
        return Api.Scoreboard.setScoreboardLockQuarter({center, quarter, data}).done(() => {
            dispatch(scoreboardLockData.saveState('loaded'))
            if (clear) {
                // Typically, we want to get rid of the data
                delayDispatch(dispatch, scoreboardLockData.resetAll())
            }
        })
    }
}
