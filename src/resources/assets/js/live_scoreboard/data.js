import { ReduxFriendlyScoreboard } from '../reusable/scoreboard'
import { LoadingMultiState } from '../reusable/reducers'

export const scoreboardLoad = new LoadingMultiState('live_scoreboard/loadState')
export const submitLoad = new LoadingMultiState('live_scoreboard/submitState')

export const redux_scoreboard = new ReduxFriendlyScoreboard()
