import { actions as formActions } from 'react-redux-form'

import { SCOREBOARDS_FORM_KEY, SCOREBOARD_SAVED, scoreboardLoad, scoreboardSave, annotateScoreboards } from './data'

export const loadState = scoreboardLoad.actionCreator()
const saveState = scoreboardSave.actionCreator()

export function loadScoreboard(centerId, reportingDate) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        return Api.Scoreboard.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true
        }).done((data) => {
            dispatch(initializeScoreboard(data))
        }).fail(() => {
            dispatch(loadState('failed'))
        })
    }
}

export function initializeScoreboard(data) {
    return (dispatch) => {
        dispatch(loadState('loaded'))
        dispatch(formActions.change(SCOREBOARDS_FORM_KEY, annotateScoreboards(data), {silent: true}))
    }
}

export function saveScoreboards(centerId, reportingDate, toSave, scoreboards) {
    return (dispatch, _, { Api }) => {
        const candidate = toSave[0]

        dispatch(saveState('loading'))
        return Api.Scoreboard.stash({
            center: centerId,
            reportingDate: reportingDate,
            data: scoreboards[candidate]
        }).done((data) => {
            if (data.success) {
                dispatch(scoreboardSaved(toSave[0]))
                dispatch(saveState('loaded'))
            } else {
                dispatch(saveState({error: data.error || 'error'}))
            }
        }).fail(() => {
            dispatch(saveState('failed'))
        })
    }
}

function scoreboardSaved(key) {
    return {type: SCOREBOARD_SAVED, payload: key}
}
