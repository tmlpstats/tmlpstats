import React from 'react'
import { connect } from 'react-redux'
import { GAME_KEYS } from '../reusable/scoreboard'

import GameRow from './GameRow'
import * as actions from './actions'

const settings = window.settings
const moment = window.moment // TODO consider ponyfill

class LiveScoreboardView extends React.Component {
    componentWillMount() {
        // Start data fetch request as soon as the component loads
        this.props.dispatch(actions.getCurrentScores(settings.center.abbreviation))
    }

    render() {
        const { loading, scoreboard } = this.props
        if (!loading.loaded) {
            return <div>Loading</div>
        }
        var date = 'Date unknown'
        if (scoreboard.week) {
            const sw = moment(scoreboard.week).format('MMM D')
            const m = scoreboard.meta.updatedAt
            const updatedWeek = moment(m? m.date : undefined).format('MMM D h:mm a')
            date = `Promises for ${sw}; data updated ${updatedWeek}`
        }
        const { rating, points } = scoreboard

        const games = GAME_KEYS.map((game) => {
            return <GameRow key={game} game={game} data={scoreboard.games[game]} editable={settings.LiveScoreboard.editable} />
        })

        return (
            <div className="table-responsive">
                <table className="table table-condensed table-bordered table-striped centerStatsSummaryTable">
                    <thead>
                        <tr className="border-top-thin">
                            <th rowSpan="2">&nbsp;</th>
                            <th colSpan="5">{date}</th>
                        </tr>
                        <tr>
                            <th className="promise">P</th>
                            <th>A</th>
                            <th>Gap</th>
                            <th>%</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        {games}
                        <tr className="border-top">
                            <th colSpan="4">{rating}</th>
                            <th className="total">Total:</th>
                            <th>{points}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        )
    }
}

const mapStateToProps = (state) => state.live_scoreboard

const LiveScoreboard = connect(mapStateToProps)(LiveScoreboardView)

export default LiveScoreboard
