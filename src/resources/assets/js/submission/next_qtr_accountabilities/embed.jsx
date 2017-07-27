import React, { Component } from 'react'

import { connectRedux } from '../../reusable/dispatch'

import checkCoreData from '../core/checkCoreData'
import { QuarterAccountabilitiesTable, TopAlert } from './components'

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
                <TopAlert />
                <QuarterAccountabilitiesTable params={{centerId, reportingDate}} autoSave={true} />
            </div>
        )
    }
}
