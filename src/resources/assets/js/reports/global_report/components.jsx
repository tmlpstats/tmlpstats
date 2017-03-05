import React, { Component } from 'react'
import { createSelector } from 'reselect'
import ReportsMeta from '../meta'
import { TabbedReport } from '../tabbed_report/components'
import { filterReportFlags, makeTwoTabParamsSelector } from '../tabbed_report/util'
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
        this.makeTabParams = makeTwoTabParamsSelector()
        const { params } = props
        if (params) {
            this.showReport(params.tab2 || params.tab1)
        }
    }

    reportUriBase() {
        const { regionAbbr, reportingDate } = this.props.params
        return `/reports/regions/${regionAbbr}/${reportingDate}`
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.params !== this.props.params) {
            this.showReport(nextProps.params.tab2 || nextProps.params.tab1)
        }
    }

    reportUri(parts) {
        let tabParts = parts.join('/')
        return `${this.reportUriBase()}/${tabParts}?viewmode=react`
    }

    showReport(reportId) {
        const { regionAbbr, reportingDate } = this.props.params
        delayDispatch(this, reportData.loadReport(reportId, {region: regionAbbr, reportingDate}))
    }

    getContent(reportId) {
        return this.props.data[reportId] || ''
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
