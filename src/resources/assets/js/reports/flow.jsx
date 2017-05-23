import React from 'react'
import { Route, IndexRoute, IndexRedirect } from 'react-router'

import * as globalReport from './global_report/components'
import * as localReport from './local_report/components'

export default function ReportsFlow() {
    return (
        <Route path="/reports">
            <Route path="regions/:regionAbbr/:reportingDate">
                <IndexRedirect to="WeeklySummaryGroup" />
                <Route path=":tab1/:tab2" component={globalReport.GlobalReport} />
                <Route path=":tab1" component={globalReport.GlobalReport} />
            </Route>
            <Route path="centers/:centerId/:reportingDate">
                <IndexRedirect to="Summary" />
                <Route path=":tab1/:tab2" component={localReport.LocalReport} />
                <Route path=":tab1" component={localReport.LocalReport} />
            </Route>
        </Route>
    )
}
