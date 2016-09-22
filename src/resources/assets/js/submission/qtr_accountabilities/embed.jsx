import React, { Component } from 'react'
import { checkCoreData } from '../core/SubmissionFlowRoot'
import { connectRedux } from '../../reusable/dispatch'
import { QuarterAccountabilitiesTable } from './components'

// use jQuery because we can get settings this way.
const { settings } = window

@connectRedux()
export default class Blah extends Component {
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
        return <QuarterAccountabilitiesTable />
    }
}
