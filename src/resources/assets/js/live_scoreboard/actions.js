import objectPick from 'lodash/pick'

import { objectAssign } from '../reusable/ponyfill'
import Api from '../api'

import { scoreboardLoad, redux_scoreboard } from './data'


const loadState = scoreboardLoad.actionCreator()

const DESIRED_SB_KEYS = ['games', 'meta', 'week']

export function getCurrentScores(center) {
    return (dispatch) => {
        dispatch(loadState('loading'))
        return Api.LiveScoreboard.getCurrentScores({center}).then((data) =>{
            dispatch(updateScoreboard(objectPick(data, DESIRED_SB_KEYS)))
            dispatch(loadState('loaded'))
            return data
        })
    }
}

export function updateScoreboard(data) {
    return {
        type: 'live_scoreboard/setAll',
        payload: data
    }
}

export function changeGameField(game, field, value) {
    return {
        type: 'live_scoreboard/setGameValue',
        payload: { game, field, value }
    }
}

export function setGameOp(game, op) {
    return changeGameField(game, 'op', op)
}

export function postGameValue(center, game, type, value) {
    return (dispatch, getState) => {
        const request = { center, game, type, value }
        return Api.LiveScoreboard.setScore(request).then((data) => {
            if (data.games) {
                const existing = getState().live_scoreboard.scoreboard
                const newMeta = objectAssign({}, existing.meta, data.meta)
                const newSb = redux_scoreboard.mergeGameUpdates(existing, data.games)
                dispatch(updateScoreboard(objectAssign(newSb, {meta: newMeta})))
            }
            return data
        })
    }
}

export function submitUpdates(center, game, field, gameValue) {
    return (dispatch, getState) => {
        dispatch(setGameOp(game, 'updating'))
        const makeRevert = () => {
            return ifGeneration(getState, game, () => {
                dispatch(setGameOp(game, 'default'))
            })
        }
        const successHandler = (data) => {
            dispatch(setGameOp(game, 'success'))
            setTimeout(makeRevert(), 5000)
            return data
        }
        const failHandler = () => {
            dispatch(setGameOp(game, 'failed'))
            setTimeout(makeRevert(), 8000)
        }
        return dispatch(postGameValue(center, game, field, gameValue)).then(successHandler, failHandler)
    }
}

export function changeGameFieldPotentialUpdate(center, game, field, gameValue) {
    return (dispatch, getState) => {
        dispatch(changeGameField(game, field, gameValue))

        if (!isNaN(parseInt(gameValue))) {
            const cb = ifGeneration(getState, game, () => {
                dispatch(submitUpdates(center, game, field, gameValue))
            })
            setTimeout(cb, 700)
        }
    }

}

function ifGeneration(getState, game, handler) {
    const generation = generationSelector(getState(), game)
    return () => {
        if (generationSelector(getState(), game) == generation) {
            handler(...arguments)
        }
    }
}

function gameSelector(state, game) {
    return state.live_scoreboard.scoreboard.games[game]
}

function generationSelector(state, game) {
    return gameSelector(state, game)._gen
}
