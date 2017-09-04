import moment from 'moment'
import React, { PureComponent } from 'react'

class EffectivenessBase extends PureComponent {
    renderHeaders(dates, milestones) {
        const msHeaders = []
        const dateHeaders = []

        dates.forEach((dateStr) => {
            let cellText = ''
            let cellClass = 'border-right border-left'

            const date = moment(dateStr)
            if (date.isSame(milestones['classroom1Date'])) {
                cellText = 'Milestone 1'
            } else if (date.isSame(milestones['classroom2Date'])) {
                cellText = 'Milestone 2'
            } else if (date.isSame(milestones['classroom3Date'])) {
                cellText = 'Milestone 3'
            } else {
                cellClass = 'border-right-none border-left-none'
            }

            msHeaders.push(
                <th key={`ms_${dateStr}`} className={`data-point border-right ${cellClass}`}>{cellText}</th>
            )
            dateHeaders.push(
                <th key={dateStr} className="data-point border-right">{moment(date).format('MMMM D, YYYY')}</th>
            )
        })

        return (
            <thead>
                <tr>
                    <th rowSpan="2" className="middle-align border-right">Center</th>
                    {msHeaders}
                </tr>
                <tr>{dateHeaders}</tr>
            </thead>
        )
    }

    renderBody(reportData) {
        const outputData = []

        Object.keys(reportData).forEach((k) => {
            outputData.push(
                this.renderCenter(k, reportData[k])
            )
        })

        return (
            <tbody>
                {outputData}
            </tbody>
        )
    }

    renderCenter(name, centerData) {
        const weekData = []

        Object.keys(centerData).forEach((week) => {
            const data = centerData[week]
            const actualClass = (data['actual'] >= data['promise']) ? 'success' : 'bg-danger'

            weekData.push(
                <td key={week} className={`data-point border-right ${actualClass}`}>
                    {data['actual']} of {data['promise']}
                </td>
            )
        })

        return (
            <tr key={name}>
                <th key="name" className="border-right">{name}</th>
                {weekData}
            </tr>
        )
    }

    render() {
        const { initialData: { milestones, reportData } } = this.props

        if (!milestones || !reportData) {
            return <div>Loading region data...</div>
        }

        let dates = []

        Object.keys(reportData).forEach(center => {
            dates = Object.keys(reportData[center])
        })

        return (
            <div className="table-responsive">
                <br/>
                <h5>Data so far this quarter</h5>
                <table className="table table-condensed table-bordered">
                    {this.renderHeaders(dates, milestones)}
                    {this.renderBody(reportData)}
                </table>
            </div>
        )
    }
}

export class AccessToPowerEffectiveness extends EffectivenessBase {}
export class PowerToCreateEffectiveness extends EffectivenessBase {}
export class Team1ExpansionEffectiveness extends EffectivenessBase {}
export class Team2ExpansionEffectiveness extends EffectivenessBase {}
export class GameInTheWorldEffectiveness extends EffectivenessBase {}
export class LandmarkForumEffectiveness extends EffectivenessBase {}
