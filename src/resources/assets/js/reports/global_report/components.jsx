import React, { Component } from 'react'
import { createSelector } from 'reselect'
import ReportsMeta from '../meta'
import { TabbedReport } from '../tabbed_report/components'
import { filterReportFlags } from '../tabbed_report/util'
import { reportData } from './data'
import { connectRedux, delayDispatch } from '../../reusable/dispatch'

const DEFAULT_FLAGS = {afterClassroom2: false}

@connectRedux()
export class GlobalReport extends Component {
    static mapStateToProps(state) {
        return reportData.opts.findRoot(state)
    }

    constructor(props) {
        super(props)

        // XXX TODO deal with report flags actually changing after classroom 2
        this.fullReport = filterReportFlags(ReportsMeta['Global'], DEFAULT_FLAGS)

        this.makeTabParams = createSelector(
            (params) => params.tab1,
            (params) => params.tab2,
            (tab1, tab2) => {
                let tabs = [tab1]
                if (tab2) {
                    tabs.push(tab2)
                    this.showReport(tab2)
                }
                return tabs
            }
        )
    }

    reportUriBase() {
        const { regionAbbr, reportingDate } = this.props.params
        return `/reports/regions/${regionAbbr}/${reportingDate}`
    }

    reportUri(parts) {
        let tabParts = parts.join('/')
        return `${this.reportUriBase()}/${tabParts}?viewmode=react`
    }

    showReport(reportId) {
        const { regionAbbr, reportingDate } = this.props.params
        delayDispatch(this, reportData.loadReport(reportId, {region: regionAbbr, reportingDate}))
    }

    responsiveLabel(report) {
        return responsiveLabel(report)
    }

    render() {
        const tabs = this.makeTabParams(this.props.params)
        return <TabbedReport tabs={tabs} fullReport={this.fullReport} reportContext={this} />
    }

}

/** Generate tab label HTML for a report if shortName is set */
function responsiveLabel(report) {
    if (report.shortName) {
        return [
            <span className="long" key="long">{report.name}</span>,
            <span className="brief" key="brief">{report.shortName}</span>
        ]
    } else {
        return report.name
    }
}
