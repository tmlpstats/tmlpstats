import { combineReducers } from 'redux'
import { modelReducer, formReducer } from 'react-redux-form'

import { SCOREBOARDS_FORM_KEY, SCOREBOARD_SAVED, scoreboardLoad, scoreboardSave, messages } from './data'

const sfLength = SCOREBOARDS_FORM_KEY.length

function captureToSave(state=[], action) {
    if (action.type == 'rrf/change') {
        const { model } = action
        if (model.length > sfLength && model.substring(0, sfLength) == SCOREBOARDS_FORM_KEY) {
            // There has to be a better way to capture which week we're talking about, but this works for now.
            // bits should look like ['[5]', 'games', 'cap', 'actual']
            const bits = model.substring(sfLength).split('.')

            if (bits.length == 4 && bits[1] == 'games' && bits[0].length > 2) {
                const sbIndex = bits[0].slice(1, -1) // strip off the brackets, so '[5]' => '5'
                if (state.indexOf(sbIndex) == -1) {
                    return state.concat([sbIndex])
                }
            }
        }
    } else if (action.type == SCOREBOARD_SAVED) {
        if (state.indexOf(action.payload) != -1) {
            return state.filter(x => x != action.payload)
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
