import React from 'react'
import { Route, IndexRedirect } from 'react-router'

import AdminRoot from './AdminRoot'
import * as regionComponents from './regions/components'

function EmptyWrapper(props) {
    return <div>{props.children}</div>
}

export default function AdminFlow() {
    return (
        <Route path="/admin" component={AdminRoot}>
            <Route path="regions/:regionAbbr">
                <Route path="manage_scoreboards" component={regionComponents.RegionScoreboards} />
                <Route path="manage_scoreboards/from/:centerId" component={regionComponents.EditScoreboardLock} />
            </Route>
        </Route>
    )
}
