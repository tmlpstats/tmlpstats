import { delayDispatch } from '../../reusable/dispatch'
import { objectAssign } from '../../reusable/ponyfill'
import { normalize } from 'normalizr'
import { actions as formActions } from 'react-redux-form'

import Api from '../../api'
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
        data = objectAssign({}, data, {centerId, quarterId, applyCenter: [centerId]})
        // TODO decide on center / quarter info being part of the key
        console.log('got lock data', data)
        dispatch(scoreboardLockData.replaceCollection(data))
        dispatch(scoreboardLockData.loadState('loaded'))
    }
}

export function saveScoreboardLocks(center, quarter, data, clear) {
    return (dispatch) => {
        dispatch(scoreboardLockData.saveState('loading'))
        return Api.Submission.Scoreboard.setScoreboardLockQuarter({center, quarter, data}).then(() => {
            dispatch(scoreboardLockData.saveState('loaded'))
            if (clear) {
                // Typically, we want to get rid of the data
                delayDispatch(dispatch, scoreboardLockData.resetAll())
            }
        })
    }
}

export function fullyLockWeek(week) {
    return (dispatch, getState) => {
        const { idx } = indexWeek(getState, week)
        if (idx != -1){
            dispatch(formActions.remove(`${scoreboardLockData.opts.model}.reportingDates`, idx))
        }
    }
}

export function unlockWeek(week, reportingDates) {
    return (dispatch, getState) => {
        const { idx } = indexWeek(getState, week)
        if (idx == -1) {
            let output = {
                reportingDate: week,
                weeks: reportingDates.map(week=>{
                    return {
                        week: week.format('YYYY-MM-DD'),
                        editPromise: false
                    }
                })
            }

            dispatch(formActions.push(`${scoreboardLockData.opts.model}.reportingDates`, output))
        }
    }
}

function indexWeek(getState, week) {
    const rds = getState().admin.regions.scoreboardLock.data.reportingDates
    for (let i = 0; i <rds.length; i++) {
        if (rds[i].reportingDate == week) {
            return {idx: i, value: rds[i]}
        }
    }
    return {idx: -1}
}
