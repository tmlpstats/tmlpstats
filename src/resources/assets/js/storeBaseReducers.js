import { submissionReducer } from './submission/reducers'
import adminReducer from './admin/reducers'
import liveScoreboardReducer from './live_scoreboard/reducers'
import { lookupsData } from './lookups'
import reportsReducer from './reports/reducers'

const baseReducers = {
    admin: adminReducer,
    live_scoreboard: liveScoreboardReducer,
    lookups: lookupsData.reducer(),
    reports: reportsReducer,
    submission: submissionReducer,
}

export default baseReducers
