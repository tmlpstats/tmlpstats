import React from 'react'
import { Route, IndexRoute } from 'react-router'

import AdminRoot from './AdminRoot'
import * as regionComponents from './regions/components'

function EmptyWrapper(props) {
    return <div>{props.children}</div>
}

export default function AdminFlow() {
    return (
        <Route path="/admin" component={AdminRoot}>
            <Route path="regions/:regionAbbr" component={regionComponents.SelectQuarter}>
                <Route path="quarter/:quarterId">
                    <IndexRoute component={regionComponents.RegionQuarterIndex} />
                    <Route path="quarter_dates" component={regionComponents.QuarterDates} />
                    <Route path="manage_scoreboards" component={regionComponents.RegionScoreboards} />
                    <Route path="manage_scoreboards/from/:centerId" component={regionComponents.EditScoreboardLock} />
                    <Route path="accountability_rosters" component={regionComponents.AccountabilityRosters} />
                </Route>
            </Route>
        </Route>
    )
}
