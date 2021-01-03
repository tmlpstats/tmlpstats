import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { defaultMemoize } from 'reselect'
import moment from 'moment'
import { TabbedReport } from '../tabbed_report/components'
import { connectRedux, delayDispatch } from '../../reusable/dispatch'
import { RegionSystemMessages } from '../../reusable/system-messages/connected'
import { filterReportFlags, makeTwoTabParamsSelector } from '../tabbed_report/util'
import { reportConfigData } from '../data'
import ReportsMeta from '../meta'
import ReportTitle from '../ReportTitle'

import { loadConfig } from './actions'
import { reportData, GlobalReportKey } from './data'
import * as pages from './pages'

@connectRedux()
export class GlobalReport extends Component {
    static mapStateToProps() {
        const getStorageKey = defaultMemoize(params => new GlobalReportKey(params))

        return (state, ownProps) => {
            const storageKey = getStorageKey(ownProps.params)
            const reportConfig = reportConfigData.selector(state).get(storageKey)
            const reportRoot = reportData.opts.findRoot(state)
            return {
                storageKey,
                reportConfig,
                lookupsLoad: state.lookups.loadState,
                pageData: reportRoot.data
            }
        }
    }

    static propTypes = {
        storageKey: PropTypes.instanceOf(GlobalReportKey),
        reportConfig: PropTypes.object,
        lookupsLoad: PropTypes.object,
        pageData: PropTypes.object
    }

    constructor(props) {
        super(props)
        this.makeTabParams = makeTwoTabParamsSelector()
        this.componentWillReceiveProps(props)
    }

    reportUriBase() {
        const { regionAbbr, reportingDate } = this.props.params
        return `/reports/regions/${regionAbbr}/${reportingDate}`
    }

    componentWillReceiveProps(nextProps) {
        const { storageKey, reportConfig, lookupsLoad, dispatch, params } = nextProps
        if (params !== this.props.params || storageKey != this._savedKey) {
            this.showReport(nextProps.params.tab2 || nextProps.params.tab1)
            this._savedKey = storageKey
        }
        if (!reportConfig) {
            if (lookupsLoad.available) {
                dispatch(loadConfig(storageKey))
            }
        } else if (reportConfig !== this.props.reportConfig) {
            const r = this.fullReport = filterReportFlags(ReportsMeta['Global'], reportConfig.flags)
            dispatch(reportData.init(r, storageKey))
            this.showReport(nextProps.params.tab2 || nextProps.params.tab1)
        }
    }

    reportUri(parts) {
        let tabParts = parts.join('/')
        return `${this.reportUriBase()}/${tabParts}`
    }

    showReport(reportId) {
        if (!this.fullReport) {
            setTimeout(() => this.showReport(reportId), 200)
            return
        }
        const report = this.fullReport[reportId]
        if (!report) {
            alert('Unknown report page: ' + reportId)
        } else if (report.type != 'grouping') {
            const { regionAbbr, reportingDate } = this.props.params
            delayDispatch(this, reportData.loadReport(reportId, {region: regionAbbr, reportingDate}))
        }
    }

    pageComponent(report) {
        return pages[report.id]
    }

    getContent(reportId) {
        return this.props.pageData.get(reportId) || ''
    }

    responsiveLabel(report) {
        return responsiveLabel(report)
    }

    render() {
        if (!this.fullReport) {
            return <div>Loading...</div>
        }

        const { params, reportConfig: config } = this.props
        const tabs = this.makeTabParams(params)

        let dateStr = moment(params.reportingDate).format('MMM D, YYYY')
        const title = `${config.regionInfo.name} - ${dateStr}`

        let nav
        if (config.capabilities.reportNavLinks) {
            nav = (
                <div className="row goBackLink">
                    <div className="col-sm-8">
                        <a href="/home">&laquo; Go Back</a>
                    </div>
                </div>
            )
        }

        return (
            <div>
                <ReportTitle title={title} reportToken={config.reportToken} nav={nav} />
                <RegionSystemMessages region={this.props.params.regionAbbr} section="global_report" />
                <TabbedReport tabs={tabs} fullReport={this.fullReport} reportContext={this} reportConfig={config} />
            </div>
        )
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
