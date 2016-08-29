import { scoreboardLoad, redux_scoreboard } from './data'

const loadState = scoreboardLoad.actionCreator()

export function getCurrentScores(center) {
    return (dispatch, _, { Api }) => {
        dispatch(loadState('loading'))
        Api.LiveScoreboard.getCurrentScores({center}, function (data) {
            const { games, meta } = data
            dispatch(updateScoreboard({games, meta}))
            dispatch(loadState('loaded'))
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
    return (dispatch, getState, { Api }) => {
        const request = { center, game, type, value }
        return Api.LiveScoreboard.setScore(request, function (data) {
            if (data.games) {
                const existing = getState().live_scoreboard.scoreboard
                dispatch(updateScoreboard(redux_scoreboard.mergeGameUpdates(existing, data.games)))
            }
        })
    }
}
