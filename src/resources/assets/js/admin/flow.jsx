import React from 'react'
import { Route } from 'react-router'

import AdminRoot from './AdminRoot'
import * as regionComponents from './regions/components'
import * as systemComponents from './system/components'
import * as homeComponents from './home'

function EmptyWrapper(props) {
    return <div>{props.children}</div>
}

function GraphQLFlow() {
    if (process.env.NODE_ENV !== 'production') {
        const gqlView = require('./gql-view')
        return (
            <Route path="graphql" component={gqlView.Interface} />
        )
    }
}

export function AdminFlow() {
    return (
        <Route path="/admin" component={AdminRoot}>
            <Route path="regions/:regionAbbr" component={regionComponents.SelectQuarter}>
                <Route path="quarter/:quarterId">
                    <Route path="quarter_dates" component={regionComponents.QuarterDates} />
                    <Route path="manage_scoreboards" component={regionComponents.RegionScoreboards} />
                    <Route path="manage_scoreboards/from/:centerId" component={regionComponents.EditScoreboardLock} />
                    <Route path="accountability_rosters" component={regionComponents.AccountabilityRosters} />
                </Route>
            </Route>
            <Route path="system">
                <Route path="system_messages" component={systemComponents.AdminSystemMessages} />
                <Route path="system_messages/:id" component={systemComponents.EditSystemMessage} />
            </Route>
            {GraphQLFlow()}
        </Route>
    )
}

export function HomeFlow() {
    return (
        <Route path="/home/:regionAbbr(/:reportingDate)" component={homeComponents.HomeHeader} />
    )
}
