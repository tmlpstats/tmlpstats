import React, { Component, PureComponent, PropTypes } from 'react'
import { Link } from 'react-router'
import { defaultMemoize } from 'reselect'
import moment from 'moment'

import { connectRedux, delayDispatch } from '../../reusable/dispatch'
import { loadStateShape } from '../../reusable/shapes'
import { lookupsData } from '../../lookups'
import { TabbedReport } from '../tabbed_report/components'
import { filterReportFlags, makeTwoTabParamsSelector } from '../tabbed_report/util'
import ReportsMeta from '../meta'
import { reportConfigData } from '../data'
import ReportTitle from '../ReportTitle'

import * as actions from './actions'
import * as pages from './pages'
import { reportData, LocalKey } from './data'

const regionCentersData = lookupsData.scopes.region_centers

@connectRedux()
export class LocalReport extends Component {
    static mapStateToProps() {
        const getStorageKey = defaultMemoize((params) => new LocalKey(params))

        // mapStateToProps returns a function to allow per-instance memoization
        return (state, ownProps) => {
            const { local_report } = state.reports
            const storageKey = getStorageKey(ownProps.params)
            const reportConfig = reportConfigData.selector(state).get(storageKey)
            return {
                storageKey,
                reportConfig,
                pageData: local_report.reportData.data,
                lookupsLoad: state.lookups.loadState,
            }
        }
    }

    static propTypes = {
        storageKey: PropTypes.instanceOf(LocalKey),
        pageData: PropTypes.object,
        lookupsLoad: loadStateShape,
        reportConfig: PropTypes.object,
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
        const { params: nextParams, dispatch, storageKey, lookupsLoad, reportConfig } = nextProps
        if (storageKey !== this.storageKey) {
            this.storageKey = storageKey
            this.showReport(nextParams.tab2 || nextParams.tab1)
        }
        if (!reportConfig) {
            if (lookupsLoad.available) {
                dispatch(actions.loadConfig(this.storageKey))
            }
        } else if (reportConfig !== this.savedConfig) {
            const r = this.fullReport = filterReportFlags(ReportsMeta['Local'], reportConfig.flags)
            this.savedConfig = reportConfig
            dispatch(reportData.init(r, this.storageKey))
            this.showReport(nextParams.tab2 || nextParams.tab1)
        }
    }

    reportUriBase(key) {
        return reportUriBase(key || this.props.params)
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
        const key = this.storageKey.set('page', reportId)
        return this.props.pageData.get(key)
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
            delayDispatch(this, reportData.loadReport(reportId, this.storageKey.queryParams(), this.storageKey))
        }
    }

    responsiveLabel(report) {
        return responsiveLabel(report)
    }

    pageComponent(report) {
        return pages[report.id]
    }

    render() {
        const { params, reportConfig: config } = this.props
        if (!this.fullReport || !config) {
            return <div>Loading...</div>
        }
        const tabs = this.makeTabParams(params)

        let nav, messages
        if (config.capabilities.reportNavLinks) {
            nav = <ReportCenterNav params={params} regionId={config.globalRegionId} />
        }

        const dateStr = moment(params.reportingDate).format('MMM D, YYYY')
        const title = `${config.centerInfo.name} - ${dateStr}`

        return (
            <div>
                <ReportTitle title={title} reportToken={config.reportToken} nav={nav} />
                {messages}
                <TabbedReport tabs={tabs} fullReport={this.fullReport} reportContext={this} />
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


@connectRedux()
class ReportCenterNav extends PureComponent {
    static mapStateToProps() {
        const getRegionCentersList = defaultMemoize((x) => x? x.toList() : null)

        return (state, ownProps) => {
            const regionCenters = regionCentersData.selector(state).get(ownProps.regionId)
            return {
                regionCenters,
                regionCentersList: getRegionCentersList(regionCenters)
            }
        }
    }

    static propTypes = {
        regionCenters: PropTypes.object,
        regionCentersList: PropTypes.object,
        regionId: PropTypes.string,
    }

    render() {
        const { regionCentersList: all, dispatch, params } = this.props

        if (!all) {
            dispatch(regionCentersData.load(this.props.regionId))
        }
        const centerId = params.centerId.toLowerCase()

        let prev
        let next
        if (all) {
            let index = all.findIndex((rc) => rc.abbreviation == centerId)
            if (index > 0) {
                const target = all.get(index - 1)
                prev = (
                    <Link to={this.makeUri(target)} className="btn btn-default">&laquo; {target.name}</Link>
                )
            }
            const nextTarget = all.get(index + 1)
            if (nextTarget) {
                next = (
                    <Link to={this.makeUri(nextTarget)} className="btn btn-default">{nextTarget.name} &raquo;</Link>
                )
            }
        }
        return (
            <div className="row goBackLink">
                <div className="col-sm-8">
                    <a href="/home">&laquo; See All</a>
                </div>
                <div className="col-sm-4" style={{textAlign: 'right'}}>
                    {prev}
                    {next}
                </div>
            </div>
        )
    }

    makeUri(center) {
        const { params } = this.props
        let uri = reportUriBase({reportingDate: params.reportingDate, centerId: center.abbreviation.toLowerCase()})
        if (params.tab1) {
            uri += '/' + params.tab1
        }
        if (params.tab2){
            uri += '/' + params.tab2
        }
        return uri
    }
}

function reportUriBase(key) {
    const { centerId, reportingDate } = (key || this.props.params)
    return `/reports/centers/${centerId}/${reportingDate}`
}


