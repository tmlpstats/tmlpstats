import { combineReducers } from 'redux'
import { objectAssign } from '../reusable/ponyfill'

import { scoreboardLoad, redux_scoreboard } from './data'


function scoreboardReducer(state={}, action) {
    switch (action.type) {
    case 'live_scoreboard/setAll':
        return redux_scoreboard.dataFromRaw(action.payload)
    case 'live_scoreboard/setGameValue':
        const { game, field, value } = action.payload
        return redux_scoreboard.updateGameField(state, game, field, value)
    }

    return state
}

const liveScoreboardReducer = combineReducers({
    loading: scoreboardLoad.reducer(),
    scoreboard: scoreboardReducer
})

export default liveScoreboardReducer
