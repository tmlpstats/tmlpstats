/**
 * Pages for the local report
 *
 * Each page must have an ID exactly equal to the report ID from the reports-generated
 */
import moment from 'moment'
import PropTypes from 'prop-types'
import React, { Component, PureComponent } from 'react'
import { defaultMemoize } from 'reselect'

import { DATE_TIME_PRETTY } from '../../reusable/time_displays'
import { buildTable } from '../../reusable/tabular'
import { lazy } from '../../reusable/dispatch'

export { Reconciliation } from './pages-reconciliation'
export { GitwSummary, TdoSummary, RppCapSummary, RppCpcSummary, RppLfSummary } from './pages-memberstats'

const buildNQATable = lazy(function() {

    function PrettyDate(props) {
        const { data } = props
        return <td className="hidden-print">{data? data.format(DATE_TIME_PRETTY) : 'unknown'}</td>
    }

    return buildTable({
        name: 'localReport_nqa',
        columns: [
            {key: 'display', label: 'Accountability', selector: nqa => nqa.accountability.display},
            {key: 'name', label: 'Name', default: 'N/A'},
            {key: 'phone', label: 'Phone', default: 'N/A'},
            {key: 'email', label: 'Email', default: 'N/A'},
            {key: 'updatedAt', label: 'Updated At', sorter: 'moment', headingClasses: ['hidden-print'], component: PrettyDate}
        ],
    })
})

export class NextQtrAccountabilities extends PureComponent {
    static propTypes = {
        initialData: PropTypes.shape({
            nqas: PropTypes.arrayOf(PropTypes.object)
        })
    }

    constructor(props) {
        super(props)
        this.datedNQAs = defaultMemoize((nqas) => {
            return nqas.map((nqa) => {
                const updatedAt = (nqa.meta && nqa.meta.updatedAt)? moment.utc(nqa.meta.updatedAt).local() : null
                return {updatedAt, ...nqa}
            })
        })
    }

    render() {
        const { initialData } = this.props
        const NQATable = buildNQATable()
        const nqas = this.datedNQAs(initialData.nqas)
        return (
            <div className="table-responsive">
                <h3>Next Quarter Accountabilities - {this.props.reportContext.savedConfig.centerInfo.name}</h3>
                <NQATable data={nqas} tableClasses="table table-condensed table-striped table-hover" />
            </div>
        )
    }
}
