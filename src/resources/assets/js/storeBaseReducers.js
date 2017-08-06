import { submissionReducer } from './submission/reducers'
import adminReducer from './admin/reducers'
import liveScoreboardReducer from './live_scoreboard/reducers'
import { lookupsData } from './lookups'
import reportsReducer from './reports/reducers'
import tabularReducer from './reusable/tabular/reducer'

const baseReducers = {
    admin: adminReducer,
    live_scoreboard: liveScoreboardReducer,
    lookups: lookupsData.reducer(),
    reports: reportsReducer,
    submission: submissionReducer,
    tabular: tabularReducer
}

export default baseReducers
