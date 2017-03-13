import React, { Component } from 'react'

import { connectRedux, delayDispatch } from '../../reusable/dispatch'
import { TabbedReport } from '../tabbed_report/components'
import { filterReportFlags, makeTwoTabParamsSelector } from '../tabbed_report/util'
import ReportsMeta from '../meta'

import * as actions from './actions'
import { reportData, LocalKey } from './data'

@connectRedux()
export class LocalReport extends Component {
    static mapStateToProps() {
        return (state) => {
            const { local_report: local, reportConfig } = state.reports
            return {
                pageData: local.reportData.data,
                config: reportConfig
            }
        }
    }

    constructor(props) {
        super(props)
        this.makeTabParams = makeTwoTabParamsSelector()
        // componentWillReceiveProps is not called on construction, it's easier to run the logic one time
        this.componentWillReceiveProps(props)
    }

    /**
     * componentWillReceiveProps is called whenever props change, or similarly redux props.
     * In the case of this report, it does a few things:
     *   1. If the params change (URL changes) then bump the selected report to top of queue.
     *   2. Fetches the report config and filters the report based on flags.
     */
    componentWillReceiveProps(nextProps) {
        const { params: nextParams, dispatch } = nextProps
        if (!this.storageKey || nextParams !== this.props.params) {
            this.storageKey = new LocalKey(nextParams) // will only grab centerId and reportingDate
            this.showReport(nextParams.tab2 || nextParams.tab1)
        }
        const { loadState, data } = nextProps.config
        const config = data.get(this.storageKey)
        if (!config) {
            if (loadState.available) {
                dispatch(actions.loadConfig(this.storageKey))
            }
        } else if (config !== this.savedConfig) {
            const r = this.fullReport = filterReportFlags(ReportsMeta['Local'], config.flags)
            this.savedConfig = config
            dispatch(reportData.init(r))
        }
        return config
    }

    reportUriBase() {
        const { centerId, reportingDate } = this.props.params
        return `/reports/centers/${centerId}/${reportingDate}`
    }

    /**
     * Required by the TabbedReport interface; builds a URI for a given tab
     * @param  array   parts URL parts; ['tab1Name', 'tab2Name']
     * @return string  The URI
     */
    reportUri(parts) {
        let tabParts = parts.join('/')
        return `${this.reportUriBase()}/${tabParts}`
    }

    /**
     * Required by the TabbedReport interface; gets page content
     * @param  string  reportId  The report's identifier e.g. 'FooBar'
     * @return string|object     The data for the page content.
     */
    getContent(reportId) {
        return this.props.pageData[reportId] || ''
    }

    // Dispatches an action on delay to prioritize/load a given report tab.
    showReport(reportId) {
        if (!this.fullReport) {
            setTimeout(() => this.showReport(reportId), 200)
            return
        }
        const report = this.fullReport[reportId]
        if (!report) {
            alert('Unknown report page: ' + reportId)
        } else if (report.type != 'grouping') {
            const params = this.storageKey.queryParams()
            delayDispatch(this, reportData.loadReport(reportId, params))
        }
    }

    responsiveLabel(report) {
        return responsiveLabel(report)
    }

    render() {
        if (!this.fullReport) {
            return <div>Loading...</div>
        }
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
