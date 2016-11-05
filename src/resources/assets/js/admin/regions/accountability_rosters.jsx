import React from 'react'
import { Link } from 'react-router'

import { objectAssign } from '../../reusable/ponyfill'
import { connectRedux } from '../../reusable/dispatch'
import { Alert } from '../../reusable/ui_basic'

import { checkCoreData } from '../../submission/core/SubmissionFlowRoot'
import { repromisableAccountabilities } from '../../submission/next_qtr_accountabilities/selectors'
import RegionBase from './RegionBase'
import { extraData } from './data'


function loadRosterData(dispatch, extra, { centers, regionQuarter }) {
    const quarterEnd = regionQuarter.endWeekendDate

    if (extra.loadState.state == 'loading') {
        return false
    }

    // prime the by-accountability list
    let byAccountability = {}
    let toDispatch = []
    centers.forEach((center) => {
        const key = `${center.abbreviation}/${quarterEnd}`
        const accountabilities = extra.data[key]
        if (!accountabilities) {
            if (extra.loadState.available) {
                toDispatch.push({center: center.abbreviation, reportingDate: quarterEnd})
            }
        } else {
            accountabilities.forEach((item) => {
                let ba = byAccountability[item.id]
                if (!ba) {
                    byAccountability[item.id] = ba = {}
                }
                ba[center.abbreviation] = item
            })
        }
    })

    if (toDispatch.length) {
        setTimeout(() => {
            extraData.runInGroup(dispatch, 'centerAccountabilities', () => {
                return Promise.all(toDispatch.map((params) => {
                    return dispatch(extraData.runNetworkAction('centerAccountabilities', params))
                }))
            })
        })
        return false
    }
    return byAccountability
}

@connectRedux()
export class AccountabilityRosters extends RegionBase {
    static mapStateToProps(state) {
        const accountabilities = repromisableAccountabilities(state)
        const submissionCore = state.submission.core
        return objectAssign({ accountabilities, submissionCore }, state.admin.regions)
    }

    checkData() {
        if (!this.checkRegions()) {
            return false
        }
        const { submissionCore } = this.props
        if (!submissionCore.coreInit.loaded) {
            const { centers, regionQuarter } = this.regionCenters()
            setTimeout(() => {
                checkCoreData(centers[0].abbreviation, regionQuarter.endWeekendDate, submissionCore, this.props.dispatch)
            })
            return false
        }
        return true
    }

    render() {
        if (!this.checkData()) {
            return <div>Loading prerequisites...</div>
        }
        const regionCenters = this.regionCenters()

        const data = loadRosterData(this.props.dispatch, this.props.extra, regionCenters)
        if (data === false) {
            return <div>Loading rosters...</div>
        }

        const rosters = this.props.accountabilities.map((acc) => {
            const centerData = data[acc.id] || {}
            return (
                <AccountabilityRoster
                    key={acc.id} accountability={acc} data={centerData}
                    allCenters={regionCenters.centers} />
            )
        })
        return (
            <div>
                <Alert alert="info">
                    <span>This is designed to be printed, make sure to hit "print preview" to check how it looks.</span>
                    <p>The first page will be wasted, so skip it if you need to conserve paper.</p>
                </Alert>
                <hr style={{pageBreakAfter: 'always'}} />
                {rosters}
            </div>
        )
    }
}

class AccountabilityRoster extends RegionBase {
    render() {
        const rows = this.props.allCenters.map((center) => {
            const data = this.props.data[center.abbreviation] || {}
            const dest = `/center/${center.abbreviation}/next_qtr_accountabilities`
            return (
                <tr key={center.id}>
                    <td>
                        <span className="visible-print-inline">{center.name}</span>
                        <span className="hidden-print"><Link to={dest}>{center.name}</Link></span>
                    </td>
                    <td>{data.name}</td>
                    <td>{data.phone}</td>
                    <td>{data.email}</td>
                </tr>
            )
        })
        return (
            <div style={{pageBreakAfter: 'always'}}>
                <h2>{this.props.accountability.display}</h2>
                <table className="table table-condensed">
                    <thead>
                        <tr>
                            <th>Center</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows}
                    </tbody>
                </table>
            </div>
        )
    }
}
