/**
 * RECONCILIATION REPORT FOR WEEKEND SATURDAY
 *
 * This was split out into its own file because it's a hugely complex report with a lot of interactivity.
 *
 * Currently, this is a hodge-podge. It uses a few admin APIs right now to pull useful lookup data into the redux tree,
 * therefore it is a far more complicated pair of components than thought. Also, a lot of DRY violation.
 * Please don't consider the code-style in this file 'canonical' - rather this was a "get it done" kind of thing.
 *
 * Basically, what this report does is display totals of people by quarter in a way easy to scan through
 * person by person and quarter by quarter to reconcile with Global Logistics. It pulls data on the quarters
 * and the region (poorly) and uses it to display info.
 *
 */
import Immutable from 'immutable'
import PropTypes from 'prop-types'
import React, { Component, PureComponent } from 'react'

import { buildTable } from '../../reusable/tabular'
import { connectRedux, lazy } from '../../reusable/dispatch'
import { Glyphicon } from '../../reusable/ui_basic'

import { lookupsData } from '../../lookups/manager'
import { getLabelTeamMember } from '../../submission/core/selectors'
import { checkQuartersData, checkRegionData } from '../../admin/regions/checkers'
import { joinedRegionQuarters } from '../../admin/regions/selectors'

@connectRedux()
export class Reconciliation extends Component {
    static mapStateToProps(state, ownProps) {
        // We're going to re-use a lot of selectors & checkers from the admin views for this page, for expediency.
        // This means that for now, this tab is admin-only (already guarded by a flag in reports.yml)
        const reportConfig = ownProps.reportContext.props.reportConfig
        const regionQuarters = joinedRegionQuarters(state, reportConfig.globalRegionId)
        const { regions } = state.admin.regions
        const uiState = buildTools().data.selector(state)
        return {
            quarters: state.admin.lookups.quarters,
            regionQuarters,
            regions,
            reportConfig,
            uiState
        }
    }

    static propTypes = {
        initialData: PropTypes.shape({
            byQuarter: PropTypes.objectOf(PropTypes.arrayOf(PropTypes.object)), // quarterId -> [ team_member, team_member, ...]
        }),
        reportContext: PropTypes.shape({props: PropTypes.object}), // Actually a component instance of LocalReport
        reportConfig: PropTypes.shape({globalRegionId: PropTypes.string}),
        regionQuarters: PropTypes.instanceOf(Immutable.OrderedMap),

    }

    constructor(props) {
        super(props)
        this.tools = buildTools()
    }

    checkData() {
        return checkQuartersData(this) && checkRegionData(this, this.props.reportConfig.globalRegionId)
    }

    render() {
        if (!this.checkData()) {
            return <div>Loading region data....</div>
        }

        const { regionQuarters, initialData: { byQuarter } } = this.props

        let tables = []
        regionQuarters.forEach((rq) => {
            const quarterId = rq.quarter.id
            if (!byQuarter[quarterId]) {
                return // skip this quarter
            }
            const completionQuarter = regionQuarters.get(rq.completionQuarterId)

            let verb
            let candidate
            if (completionQuarter) {
                verb = 'Completing'
                candidate = completionQuarter
            } else {
                verb = 'Started'
                candidate = rq
            }
            const uiState = this.getUiState(quarterId)
            const toggleExpand = this.toggleState.bind(this, quarterId, 'expand')
            const toggleShowUncounted = this.toggleState.bind(this, quarterId, 'uncounted')

            let data = byQuarter[quarterId]

            tables.push(
                <div key={rq.id}>
                    <h4>
                        {verb} ({candidate.startWeekendDate})
                        <a className="btn btn-default" href={`#toggle${quarterId}`} onClick={toggleExpand}><Glyphicon icon={uiState.expand? 'minus' : 'plus'} /></a>
                        <a href="#" className={'btn btn-' + (uiState.uncounted? 'danger' : 'default')} onClick={toggleShowUncounted}>
                            {uiState.uncounted? 'Hide' : 'Show'} Uncounted
                        </a>
                    </h4>
                    <ReconciliationTable regionQuarter={rq} data={data} uiState={uiState} />
                </div>
            )
        })
        return <div>{tables}</div>
    }

    getUiState(quarterId) {
        return this.props.uiState.get(quarterId, DEFAULT_UI_STATE)
    }

    toggleState(quarterId, stateKey, event) {
        event.preventDefault()
        console.log('togglestate', quarterId)
        let current = this.getUiState(quarterId)
        this.props.dispatch(this.tools.data.replaceItem(quarterId, current.set(stateKey, !current[stateKey])))
    }
}

class ReconciliationTable extends PureComponent {
    static propTypes = {
        regionQuarter: PropTypes.object,
        data: PropTypes.arrayOf(PropTypes.object),
        uiState: PropTypes.object
    }

    constructor(props) {
        super(props)
        this.tools = buildTools()
    }

    render() {
        console.log('render reconciliationtable', this.props.regionQuarter.id)
        let { data, uiState } = this.props
        if (!data) {
            data = []  // just in case
        }
        const isTeamMember = (data.length && data[0].teamMember)? true : false // if false, this is an application

        // All of this filtering stuff would be better in a selector, but there's no time.
        if (!uiState.uncounted) {
            if (isTeamMember) {
                data = data.filter(item => (!item.withdrawCodeId && !item.xferOut && !item.wbo))
            } else {
                data = data.filter(item => (item.apprDate && !item.withdrawCodeId))
            }
        }

        // fill the 'by team year' objects
        let byTeamYear = {1: [], 2: []}
        data.forEach((row) => {
            const teamYear = (isTeamMember)? row.teamMember.teamYear : row.registration.teamYear
            byTeamYear[teamYear].push(row)
        })


        if (!uiState.expand) {
            return (
                <div>
                    <b>Total</b>: {data.length}<br/>
                    <b>T1</b>: {byTeamYear[1].length}<br/>
                    <b>T2</b>: {byTeamYear[2].length}
                </div>
            )
        }

        const TableComponent = this.tools[isTeamMember? 'tm' : 'app']
        return <div><TableComponent data={data} tableClasses='table table-hover table-condensed' /></div>
    }
}


const buildTools = lazy(function() {
    // Super yuck... but speed is of the essence.
    const rqTempData = lookupsData.addScope({
        scope: 'reconciliation',
    })

    return {
        data: rqTempData,
        'tm': buildTable({
            name: 'localReport_Reconciliation_tm',
            columns: [
                {key: 'teamYear', label: 'Team Year', selector: (tmd) => tmd.teamMember.teamYear},
                {key: 'name', label: 'Name', selector: getLabelTeamMember},
                {
                    key: 'status', label:'Status',
                    selector(tmd) {
                        // I'm sure we have this function somewhere else, but time... bleh
                        if (tmd.withdrawCodeId) {
                            return `Withdrawn ${tmd.updatedAt}`
                        } else if (tmd.xferOut) {
                            return `Xfer Out`
                        } else if (tmd.wbo) {
                            return `Well-Being issue`
                        }
                    }
                },
                {key: 'comment', label: 'Comment', default: 'N/A'},
            ],
        }),
        'app': buildTable({
            name: 'localReport_Reconciliation_app',
            columns: [
                {key: 'teamYear', label: 'Team Year', selector: (app) => app.registration.teamYear, sorter: 'number'},
                {
                    key: 'name', label: 'Name',
                    selector: (app) => {
                        const person = app.registration.person
                        return `${person.firstName} ${person.lastName}`
                    }
                },
                {
                    key: 'status', label: 'Status',
                    selector(app) {
                        // I'm sure we have this function somewhere else, but this is the most brief way.
                        if (app.withdrawCodeId) {
                            return `Withdrawn ${app.wdDate}`
                        } else if (!app.apprDate) {
                            return 'Not Approved'
                        } else {
                            return 'OK'
                        }
                    }
                },
                {key: 'comment', label: 'Comment', default: 'N/A'},
            ]
        }),
    }
})

const ReconciliationUi = Immutable.Record({
    expand: false,
    uncounted: false
})

const DEFAULT_UI_STATE = new ReconciliationUi({})
