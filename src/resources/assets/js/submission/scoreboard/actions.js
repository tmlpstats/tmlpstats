import { actions as formActions } from 'react-redux-form'

import Api from '../../api'
import { markStale } from '../review/actions'

import { SCOREBOARDS_FORM_KEY, SCOREBOARD_SAVED, scoreboardLoad, scoreboardSave, annotateScoreboards, messages } from './data'

export const loadState = scoreboardLoad.actionCreator()
const saveState = scoreboardSave.actionCreator()

export function loadScoreboard(centerId, reportingDate) {
    return (dispatch) => {
        dispatch(loadState('loading'))
        return Api.Submission.Scoreboard.allForCenter({
            center: centerId,
            reportingDate: reportingDate,
            includeInProgress: true
        }).then((data) => {
            dispatch(initializeScoreboard(data))
            return data
        }).catch(() => {
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

export function saveScoreboards(centerId, reportingDate, toSaveItems, scoreboards) {
    return (dispatch) => {
        const candidate = toSaveItems.entrySeq().first()

        dispatch(saveState('loading'))
        return Api.Submission.Scoreboard.stash({
            center: centerId,
            reportingDate: reportingDate,
            data: scoreboards[candidate[0]]
        }).then((data) => {
            // Probably we don't need to check data.success anymore, as any errors should go into the 'catch' flow.
            if (data.success) {
                dispatch(scoreboardSaved(candidate))
                dispatch(messages.replace(data.week, data.messages))
                dispatch(saveState('loaded'))
                dispatch(markStale())
            } else {
                throw data
            }
            return data
        }).catch((err) => {
            dispatch(saveState({error: err.error || 'error'}))
        })
    }
}

function scoreboardSaved(candidate) {
    return {type: SCOREBOARD_SAVED, payload: candidate}
}
