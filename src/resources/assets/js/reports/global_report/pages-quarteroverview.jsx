import React, { PureComponent } from 'react'
import PropTypes from 'prop-types'

import Scoreboard from '../../reusable/scoreboard'

const VIEWS = {
    OVERVIEW: 'Overview',
    TURTLE: 'Turtle',
    CLOSE: 'Close'
}

export class QuarterOverviewReport extends PureComponent {
    static propTypes = {
        initialData: PropTypes.object,
    }

    state = {
        view: VIEWS.OVERVIEW
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
                    <th colSpan="6" className="text-center border-left border-right border-top">Historical Stats</th>
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
                    <th colSpan="3" className="text-center border-right">Last Qtr.</th>
                    <th colSpan="3" className="text-center border-right">Last Year</th>
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
                <td key="effectiveness" colSpan="3" rowSpan="6" className={bgColorClass+' text-center border-right border-bottom effectiveness-container'}>
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


        let lastQuarterIncrease = currentWeek.games[game].actual - lastQuarter.games[game].actual,
            lastQuarterIncreasePercentage = Math.round(((currentWeek.games[game].actual - lastQuarter.games[game].actual) / (lastQuarter.games[game].actual === 0 ? 1 : lastQuarter.games[game].actual)) * 100),
            lastYearIncrease = currentWeek.games[game].actual - lastYear.games[game].actual,
            lastYearIncreasePercentage = Math.round(((currentWeek.games[game].actual - lastYear.games[game].actual) / (lastYear.games[game].actual === 0 ? 1 : lastYear.games[game].actual)) * 100)


        return (
            <tr key={centerName+' '+game} className={rowClass+' '+bgColorClass}>
                <th key="center" className={bgColorClass+' border-right border-left'}>{centerDisplayName}</th>
                <th key="game" className={bgColorClass+' border-right'}>{game.toUpperCase()}</th>
                {msCols}
                <td key="points" className={bgColorClass+' text-center border-right'}>{points}</td>
                {effectivenessData}

                <td key="lastQuarter" className={bgColorClass+' text-center border-right'}>{lastQuarter.games[game].actual}</td>
                <td key="lastQuarterIncrease" className={bgColorClass+' text-center border-right'}>{lastQuarterIncrease <= 0 ? '' : '+'}{lastQuarterIncrease}</td>
                <td key="lastQuarterPercentage" className={bgColorClass+' text-center border-right'}>{lastQuarterIncreasePercentage <= 0 ? '' : '+'}{lastQuarterIncreasePercentage}%</td>

                <td key="lastYear" className={bgColorClass+' text-center border-right'}>{lastYear.games[game].actual}</td>
                <td key="lastYearIncrease" className={bgColorClass+' text-center border-right'}>{lastYearIncrease <= 0 ? '' : '+'}{lastYearIncrease}</td>
                <td key="lastYearPercentage" className={bgColorClass+' text-center border-right'}>{lastYearIncreasePercentage <= 0 ? '' : '+'}{lastYearIncreasePercentage}%</td>
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

    changeView = (nextView) => {
        this.setState({ view: nextView })
    }

    renderTurtle = (reportData) => {
        let centerData = []
        let count = 0
        Object.keys(reportData).forEach((center) => {
            let m3 = reportData[center]['milestone3']
            let final = reportData[center]['final']
            let nextCenter = true
            Object.keys(final.actual).forEach((game) => {
                if(final.actual[game] - m3.actual[game] >= -3 && final.actual[game] - m3.actual[game] <= 3) {
                    let rowClass, centerDisplayName
                    if (nextCenter) {
                        rowClass = 'border-top'
                        centerDisplayName = center
                    }

                    if (center === 'Total') {
                        rowClass = 'border-bottom'
                    }
                    let bgColorClass = ''
                    if (count % 2 === 0) {
                        bgColorClass = 'info-liter'
                    }

                    centerData.push(
                        <tr key={center+' '+game} className={rowClass+' '+bgColorClass}>
                            <th key="center" className={bgColorClass+' border-right border-left'}>{centerDisplayName}</th>
                            <th key="game" className={bgColorClass+' border-right'}>{game.toUpperCase()}</th>
                            <td key="promise" className={bgColorClass+' text-center'}>{final.promise[game]}</td>
                            <td key="from" className={bgColorClass+' text-center'}>{m3.actual[game]}</td>
                            <td key="to" className={bgColorClass+' text-center'}>{final.actual[game]}</td>
                            <td key="change" className={bgColorClass+' text-center'}>{final.actual[game] - m3.actual[game]}</td>

                        </tr>
                    )
                    nextCenter = false;
                }
            })

            count++

        })
        return (
            <table className="table table-condensed table-hover region-container">
                <thead>
                    <tr>
                        <th colSpan="1" className="text-center border-right border-left border-top">Team</th>
                        <th colSpan="1" className="text-center border-right border-top">Game</th>
                        <th colSpan="1" className="text-center border-right border-top">Promise</th>
                        <th colSpan="1" className="text-center border-right border-top">From</th>
                        <th colSpan="1" className="text-center border-right border-top">To</th>
                        <th colSpan="1" className="text-center border-right border-top">Change</th>
                    </tr>
                </thead>
                <tbody>{centerData}</tbody>
            </table>
        )
    }

    renderClose = (reportData) => {
        let centerData = []
        let count = 0
        Object.keys(reportData).forEach((center) => {
            let final = reportData[center]['final']
            let nextCenter = true
            Object.keys(final.actual).forEach((game) => {
                if( final.actual[game] - final.promise[game] >= -3 && final.actual[game] - final.promise[game] < 0 ) {
                    let rowClass, centerDisplayName
                    if (nextCenter) {
                        rowClass = 'border-top'
                        centerDisplayName = center
                    }

                    if (center === 'Total') {
                        rowClass = 'border-bottom'
                    }
                    let bgColorClass = ''
                    if (count % 2 === 0) {
                        bgColorClass = 'info-liter'
                    }

                    centerData.push(
                        <tr key={center+' '+game} className={rowClass+' '+bgColorClass}>
                            <th key="center" className={bgColorClass+' border-right border-left'}>{centerDisplayName}</th>
                            <th key="game" className={bgColorClass+' border-right'}>{game.toUpperCase()}</th>
                            <td key="promise" className={bgColorClass+' text-center'}>{final.promise[game]}</td>
                            <td key="actual" className={bgColorClass+' text-center'}>{final.actual[game]}</td>
                            <td key="difference" className={bgColorClass+' text-center'}>{final.actual[game] - final.promise[game]}</td>
                        </tr>
                    )
                    nextCenter = false
                }
            })

            count++

        })
        return (
            <table className="table table-condensed table-hover region-container">
                <thead>
                <tr>
                    <th colSpan="1" className="text-center border-right border-left border-top">Team</th>
                    <th colSpan="1" className="text-center border-right border-top">Game</th>
                    <th colSpan="1" className="text-center border-right border-top">Promise</th>
                    <th colSpan="1" className="text-center border-right border-top">Actual</th>
                    <th colSpan="1" className="text-center border-right border-top">Difference</th>
                </tr>
                </thead>
                <tbody>{centerData}</tbody>
            </table>
        )
    }

    render() {
        const { initialData: { reportData } } = this.props

        if (!reportData) {
            return <div>Loading region data...</div>
        }

        let regionsData = []
        let turtleData = []
        let closeData = []
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
                <div key={region} className="regionContainer">
                    <h3>{title}</h3>
                    <table className="table table-condensed table-hover region-container">
                        {this.renderHeaders()}
                        {this.renderBody(reportData[region])}
                    </table>
                </div>
            )

            turtleData.push(
                <div key={region} className="regionContainer">
                    <h3>{title}</h3>
                    {
                        this.renderTurtle(reportData[region])
                    }
                </div>
            )

            closeData.push(
                <div key={region} className="regionContainer">
                    <h3>{title}</h3>
                    {
                        this.renderClose(reportData[region])
                    }
                </div>
            )
        })

        let report = regionsData
        if (this.state.view === VIEWS.CLOSE) report = closeData
        else if (this.state.view === VIEWS.TURTLE) report = turtleData

        return (
            <div className="table-responsive quarter-overview">
                <p className="print-hide report-description">To print, use Google Chrome with scaling set to 45% and Background graphics checked.</p>
                <div className="btn-group grouping report-nav-level1" role="group">
                    <button type="button" className="btn btn-default" onClick={() => this.changeView(VIEWS.OVERVIEW)}>Overview</button>
                    <button type="button" className="btn btn-default" onClick={() => this.changeView(VIEWS.TURTLE)}>Turtle</button>
                    <button type="button" className="btn btn-default" onClick={() => this.changeView(VIEWS.CLOSE)}>Close But No Cigar</button>
                </div>
                {
                    report
                }
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
            <table>
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
