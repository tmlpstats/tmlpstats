import React, { PureComponent } from 'react'
import PropTypes from 'prop-types'

import Scoreboard from '../../reusable/scoreboard'

export class QuarterOverviewReport extends PureComponent {
    static propTypes = {
        initialData: PropTypes.object,
    }
    renderHeaders() {
        return (
            <thead>
                <tr>
                    <th colSpan="2" className="border-left-none border-top-none"></th>
                    <th colSpan="3" className="text-center border-right border-left border-top">Milestone 1</th>
                    <th colSpan="3" className="text-center border-right border-top">Milestone 2</th>
                    <th colSpan="3" className="text-center border-right border-top">Milestone 3</th>
                    <th colSpan="3" className="text-center border-right border-top">Weekend</th>
                    <th colSpan="4" className="border-right border-bottom border-top-none"></th>
                    <th colSpan="2" className="text-center border-left border-right border-top">Historical Stats</th>
                </tr>
                <tr>
                    <th className="border-right border-left">Center</th>
                    <th className="border-right">Game</th>
                    <th className="text-center border-right-thin">P</th>
                    <th className="text-center border-right-thin">A</th>
                    <th className="text-center border-right">%</th>
                    <th className="text-center border-right-thin">P</th>
                    <th className="text-center border-right-thin">A</th>
                    <th className="text-center border-right">%</th>
                    <th className="text-center border-right-thin">P</th>
                    <th className="text-center border-right-thin">A</th>
                    <th className="text-center border-right">%</th>
                    <th className="text-center border-right-thin">P</th>
                    <th className="text-center border-right-thin">A</th>
                    <th className="text-center border-right">%</th>
                    <th className="text-center border-right">Points</th>
                    <th colSpan="3" className="text-center border-right">Effectiveness</th>
                    <th className="text-center border-right">Last Qtr.</th>
                    <th className="text-center border-right">Last Year</th>
                </tr>
            </thead>
        )
    }

    renderBody(reportData) {
        const outputData = []

        let count = 0
        Object.keys(reportData).forEach((center) => {

            const scoreboards = {
                'milestone1': new Scoreboard(reportData[center]['milestone1']),
                'milestone2': new Scoreboard(reportData[center]['milestone2']),
                'milestone3': new Scoreboard(reportData[center]['milestone3']),
                'final': new Scoreboard(reportData[center]['final']),
                'lastQuarter': new Scoreboard(reportData[center]['lastQuarter']),
                'lastYear': new Scoreboard(reportData[center]['lastYear']),
            }

            Object.keys(scoreboards.milestone1.games).forEach((game) => {
                outputData.push(
                    this.renderGame(
                        center,
                        game,
                        scoreboards,
                        reportData[center]['rpp'],
                        (count % 2) == 0
                    )
                )
            })
            count++
        })

        return (
            <tbody>
                {outputData}
            </tbody>
        )
    }

    renderGame(centerName, game, scoreboards, rpp, isEvenCenter) {

        let bgColorClass = ''
        if (isEvenCenter) {
            bgColorClass = 'info-liter'
        }

        const { milestone1, milestone2, milestone3, final, lastQuarter, lastYear } = scoreboards

        let currentWeek
        if (final.games[game].actual !== null) {
            currentWeek = final
        } else if (milestone3.games[game].actual !== null) {
            currentWeek = milestone3
        } else if (milestone2.games[game].actual !== null) {
            currentWeek = milestone2
        } else if (milestone1.games[game].actual !== null) {
            currentWeek = milestone1
        }

        let rowClass
        let centerDisplayName
        let effectivenessData
        if (game === 'cap') {
            rowClass = 'border-top'
            centerDisplayName = centerName

            let points
            let rating
            if (currentWeek) {
                points = currentWeek.points()
                rating = currentWeek.rating()
            }

            effectivenessData = (
                <td key="effectiveness" colSpan="3" rowSpan="6" className={bgColorClass+' text-center border-right border-bottom quarter-overview-effectiveness-container'}>
                    <Effectiveness points={points} rating={rating} rppNet={rpp['net']} rppGross={rpp['gross']} />
                </td>
            )
        }

        if (game === 'lf') {
            rowClass = 'border-bottom'
        }

        let row = 0
        let msCols = []
        let milestones = [milestone1, milestone2, milestone3, final]
        milestones.forEach((sb) => {
            const ms = this.getGameStats(sb, game)
            msCols.push(<td key={row++} className={bgColorClass+' text-center border-right-thin'}>{ms.promise}</td>)
            msCols.push(<td key={row++} className={bgColorClass+' text-center border-right-thin'}>{ms.actual}</td>)
            msCols.push(<td key={row++} className={bgColorClass+' text-center border-right'}>{ms.percent}</td>)
        })

        let points
        if (currentWeek) {
            points = currentWeek.games[game].points()
        }

        return (
            <tr key={centerName+' '+game} className={rowClass+' '+bgColorClass}>
                <th key="center" className={bgColorClass+' border-right border-left'}>{centerDisplayName}</th>
                <th key="game" className={bgColorClass+' border-right'}>{game.toUpperCase()}</th>
                {msCols}
                <td key="points" className={bgColorClass+' text-center border-right'}>{points}</td>
                {effectivenessData}
                <td key="lastQuarter" className={bgColorClass+' text-center border-right'}>{lastQuarter.games[game].actual}</td>
                <td key="lastYear" className={bgColorClass+' text-center border-right'}>{lastYear.games[game].actual}</td>
            </tr>
        )
    }

    getGameStats(sb, game) {
        const stats = {
            promise: sb.games[game].promise,
            actual: sb.games[game].actual,
            percent: sb.games[game].percent() + '%',
        }

        if (game === 'gitw') {
            stats.promise += '%'
            stats.actual += '%'
        }

        // No data, make sure we don't prepend the '%'
        if (sb.games[game].actual === null) {
            stats.actual = null
            stats.percent = null
        }

        return stats
    }

    render() {
        const { initialData: { reportData } } = this.props

        if (!reportData) {
            return <div>Loading region data...</div>
        }

        let regionsData = []
        Object.keys(reportData).forEach((region) => {
            let title
            switch (region) {
            case 'East':
            case 'West':
                title = `${region}ern Region`
                break
            default:
                title = region
            }

            regionsData.push(
                <div key={region}>
                    <br/>
                    <h3>{title}</h3>
                    <table className="table table-condensed table-hover">
                        {this.renderHeaders()}
                        {this.renderBody(reportData[region])}
                    </table>
                </div>
            )
        })

        return (
            <div className="table-responsive">
                {regionsData}
            </div>
        )
    }
}

class Effectiveness extends PureComponent {
    static propTypes = {
        points: PropTypes.number,
        rating: PropTypes.string,
        rppNet: PropTypes.object,
        rppGross: PropTypes.object,
    }

    render() {
        const { points, rating, rppNet, rppGross } = this.props
        return (
            <table className="quarter-overview-effectiveness-table">
            <tbody>
                <tr>
                    <th colSpan="3">Reg Per Participant</th>
                </tr>
                <tr>
                    <th>CAP</th>
                    <td></td>
                    <th>CPC</th>
                </tr>
                <tr>
                    <td>{rppNet.cap.toFixed(2)}</td>
                    <th>Net</th>
                    <td>{rppNet.cpc.toFixed(2)}</td>
                </tr>
                <tr>
                    <td>{rppGross.cap.toFixed(2)}</td>
                    <td>Gross</td>
                    <td>{rppGross.cpc.toFixed(2)}</td>
                </tr>
                <tr className="border-top-thin border-bottom-thin">
                    <th colSpan="2">Points:</th>
                    <th>{points || '-'}</th>
                </tr>
                <tr>
                    <th colSpan="3">{rating || 'No Rating'}</th>
                </tr>
            </tbody>
            </table>
        )
    }
}
