import React, { Component } from 'react'
import moment from 'moment'
import { connectRedux } from '../../reusable/dispatch'


class TeamMemberStatsSummary extends Component {
    static mapStateToProps(state, ownProps) {
        const reportConfig = ownProps.reportContext.props.reportConfig
        return {
            reportConfig
        }
    }

    checkData() {
        const { initialData: { reportData } } = this.props
        return !!reportData
    }

    render() {
        if (!this.checkData()) {
            return <div>Loading region data....</div>
        }

        const { initialData: { reportData, totals } } = this.props
        const header = this.renderHeader(reportData.dates)
        const body = this.renderBody(reportData)
        const footer = this.renderFooter(reportData.dates, totals)

        return (
            <div className="table-responsive">
                <br/>
                <table className="table table-condensed table-bordered">
                    {header}
                    {body}
                    {footer}
                </table>
            </div>
        )
    }

    renderHeader(dates) {
        const headers = []
        dates.forEach((obj) => {
            headers.push(<th key={obj.date} className="data-point">{moment(obj.date).format('MMM D')}</th>)
        })

        return (
            <thead>
                <tr>
                    <th style={{verticalAlign: 'middle'}}>Name</th>
                    <th className="data-point" style={{width: '5em'}}>Team Year</th>
                    <th className="data-point" style={{width: '5em'}}>Total</th>
                    {headers}
                </tr>
            </thead>
        )
    }

    renderBody(reportData) {
        const members = reportData.members
        const dates = reportData.dates
        const rows = []
        members.forEach((m) => {
            if (m.withdrawn !== false) {
                return
            }

            const dateData = []
            dates.forEach((obj) => {
                const d = moment(obj.date).format('YYYY-MM-DD')
                const data = m[d]
                dateData.push(this.renderData(data, d))
            })

            rows.push(
                <tr key={m.member.id}>
                    <td key='name'>{m.member.firstName} {m.member.lastName}</td>
                    <td key='quarter' className="data-point">T{m.member.teamYear}Q{m.member.quarterNumber}</td>
                    <td key='total' className="data-point">{m.total} / {dates.length}</td>
                    {dateData}
                </tr>
            )
        })

        return (
            <tbody>
                {rows}
            </tbody>
        )
    }

    renderFooter(dates, totals) {
        const totalsData = []
        let total = 0
        dates.forEach((obj) => {
            const d = moment(obj.date).format('YYYY-MM-DD')
            totalsData.push(this.renderFooterData(totals[d], d))
            total += totals[d]
        })

        return (
            <tfoot>
                <tr key='totals'>
                    <th key='name' className="border-top">Totals</th>
                    <td key='quarter' className="data-point border-top"></td>
                    <td key='total' className="data-point border-top">{total} / {dates.length}</td>
                    {totalsData}
                </tr>
            </tfoot>
        )
    }

    renderFooterData(data, date) {
        return (
            <td key={date} className={`data-point border-top`}>
                <span className="numeric-glyphicon">{data}</span>
            </td>
        )
    }
}

class NumericSummary extends TeamMemberStatsSummary {
    renderData(data, date) {
        if (!data) {
            return (
                <td key={date} className="data-point active">
                    <span className="glyphicon glyphicon-minus"></span>
                </td>
            )
        }

        let className = this.getCellClass(data)

        return (
            <td key={date} className={`data-point ${className}`}>
                <span className="numeric-glyphicon">{data.value}</span>
            </td>
        )
    }

    getCellClass(data) {
        return ''
    }
}

@connectRedux()
export class GitwSummary extends TeamMemberStatsSummary {
    renderData(data, date) {
        if (!data) {
            return (
                <td key={date} className="data-point active">
                    <span className="glyphicon glyphicon-minus"></span>
                </td>
            )
        }

        if (data.value) {
            return (
                <td key={date} className="data-point success">
                    <span className="glyphicon glyphicon-ok"></span>
                </td>
            )
        }

        return (
            <td key={date} className="data-point danger">
                <span className="glyphicon glyphicon-remove"></span>
            </td>
        )
    }
}

@connectRedux()
export class TdoSummary extends NumericSummary {
    getCellClass(data) {
        if (data.value) {
            return 'success'
        }
        return 'danger'
    }
}

@connectRedux()
export class RppCapSummary extends NumericSummary {
    getCellClass(data) {
        if (data.value) {
            return 'warning'
        }
        return ''
    }
}

@connectRedux()
export class RppCpcSummary extends NumericSummary {
    getCellClass(data) {
        if (data.value) {
            return 'warning'
        }
        return ''
    }
}

@connectRedux()
export class RppLfSummary extends NumericSummary {
    getCellClass(data) {
        if (data.value) {
            return 'warning'
        }
        return ''
    }
}
