import React, { Component } from 'react'

import { connectRedux } from '../../reusable/dispatch'
import { Alert } from '../../reusable/ui_basic'

import { checkCoreData } from '../core/SubmissionFlowRoot'
import { QuarterAccountabilitiesTable } from './components'

// use window object because we can get settings this way.
const { settings } = window

@connectRedux()
export default class QtrAccountabilitiesEmbedded extends Component {
    static mapStateToProps(state) {
        return {core: state.submission.core}
    }

    render() {
        const { core, dispatch } = this.props
        const centerId = (settings.center)? settings.center.abbreviation : null
        const reportingDate = settings.reportingDate
        if (!centerId || !checkCoreData(centerId, reportingDate, core, dispatch)) {
            return <div>Loading...</div>
        }
        return (
            <div>
                <Alert alert="info">
                    After classroom 3: teams <b>must</b> fill out the following 4 accountabilities for the upcoming quarter:
                    Team 1 Team Leader, Team 2 Team Leader, Statistician, Logistics. You may also fill out any other accountabilities as well.
                </Alert>
                <QuarterAccountabilitiesTable params={{centerId, reportingDate}} autoSave={true} />
            </div>
        )
    }
}
