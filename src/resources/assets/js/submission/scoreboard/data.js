import { objectAssign } from '../../reusable/ponyfill'
import { LoadingMultiState, MessageManager } from '../../reusable/reducers'

export const SCOREBOARDS_FORM_KEY = 'submission.scoreboard.scoreboards'
export const SCOREBOARD_SAVED = 'scoreboard/saved'

export const scoreboardLoad = new LoadingMultiState('scoreboard/loadingState')
export const scoreboardSave = new LoadingMultiState('scoreboard/savingState')

export function annotateScoreboards(scoreboards) {
    return scoreboards.map((sb, idx) => {
        // Set the meta keys 'idx' and 'modelKey' but do it in a reduxy way using Object.assign.
        // This isn't strictly necessary because the data comes from AJAX anyway and not from a store,
        // but we want to be able to do this atomically in case we want to log the changes in the future.
        var meta = objectAssign({}, sb.meta, {
            idx: idx,
            modelKey: `${SCOREBOARDS_FORM_KEY}[${idx}]`
        })
        return objectAssign({}, sb, {meta: meta})
    })
}

export const messages = new MessageManager('scoreboard')
