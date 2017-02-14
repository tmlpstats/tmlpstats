import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'
import Immutable from 'immutable'

import { SCOREBOARDS_FORM_KEY, SCOREBOARD_SAVED, scoreboardLoad, scoreboardSave, messages } from './data'

const sfLength = SCOREBOARDS_FORM_KEY.length
const ToSave = Immutable.Record({items: Immutable.Map(), ctr: 0})
const DEFAULT_TOSAVE = new ToSave()

function captureToSave(state=DEFAULT_TOSAVE, action) {
    if (action.type == 'rrf/change') {
        const { model } = action
        if (model.length > sfLength && model.substring(0, sfLength) == SCOREBOARDS_FORM_KEY) {
            // There has to be a better way to capture which week we're talking about, but this works for now.
            // bits should look like ['[5]', 'games', 'cap', 'actual']
            const bits = model.substring(sfLength).split('.')

            if (bits.length == 4 && bits[1] == 'games' && bits[0].length > 2) {
                const sbIndex = bits[0].slice(1, -1) // strip off the brackets, so '[5]' => '5'
                return state.set('ctr', state.ctr + 1).setIn(['items', sbIndex], state.ctr)
            }
        }
    } else if (action.type == SCOREBOARD_SAVED) {
        const pair = action.payload
        const v = state.items.get(pair[0])
        if (v <= pair[1]) {
            return state.set('items', state.items.delete(pair[0]))
        }
    }
    return state
}

export const scoreboardReducer = combineReducers({
    toSave: captureToSave,
    loading: scoreboardLoad.reducer(),
    saving: scoreboardSave.reducer(),
    scoreboards: modelReducer(SCOREBOARDS_FORM_KEY),
    scoreboardsForm: formReducer(SCOREBOARDS_FORM_KEY),
    messages: messages.reducer(),
})
