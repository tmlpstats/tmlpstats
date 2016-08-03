import React from 'react'
import { connect } from 'react-redux'
import { Form, Field } from 'react-redux-form'

import { shallowArrayElementsEqual } from '../../reusable/compare'
import Scoreboard, { GAME_KEYS } from '../../reusable/scoreboard'
import { objectAssign } from '../../reusable/ponyfill'

import { SubmissionBase } from '../base_components'
import { loadScoreboard, saveScoreboards } from './actions'
import { SCOREBOARDS_FORM_KEY } from './data'

/**
 * SubmissionScoreboard is the root component for rendering the scoreboard view.
 */
class SubmissionScoreboard extends SubmissionBase {
    // Check the loading state of our initial data, and dispatch a loadScoreboard if we never loaded
    checkLoading() {
        if (this.props.loading.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            this.props.dispatch(loadScoreboard(centerId, reportingDate))
            return false
        }
        return (this.props.loading.state == 'loaded')
    }

    checkToSave() {
        const { saving, toSave, dispatch, scoreboards } = this.props
        if (saving.state == 'failed') {
            return // TODO, have some status loop and reap failed weeks
        }
        if (saving.state != 'loading' && toSave.length > 0) {
            const { centerId, reportingDate } = this.props.params
            setTimeout(() => {
                dispatch(saveScoreboards(centerId, reportingDate, toSave, scoreboards))
            })
        }
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        } else if (!this.props.scoreboards || !this.props.scoreboards.length) {
            // This should never happen, should it?
            return <div>No data!?!?</div>
        }
        this.checkToSave()
        var rows = []
        rowSplit(this.props.scoreboards, (row, key) => {
            rows.push(
                <div key={key} className="row">
                    <div className="col-lg-12">
                        <ScoreboardRow currentWeek={this.props.params.reportingDate} weeks={row} />
                    </div>
                </div>
            )
        })
        return (
            <Form model={SCOREBOARDS_FORM_KEY}>
                <h3>Scoreboard</h3>
                <div>{rows}</div>
            </Form>
        )
    }

}

/**
 * ScoreboardRow is a pure component (only receives props) that renders an entire row
 * of an editable scoreboard.
 *
 * Typically, what is meant by "entire row" is a group of weeks leading up to a classroom or weekend date.
 */
class ScoreboardRow extends SubmissionBase {

    // Normally, we don't have to implement shouldComponentUpdate because connect handles a lot of this for us.
    // However, the week grouping causes props mismatches and this results in a full re-render of all four week boxes
    // regardless of whether data changed in that given week box. Since week boxes also include rather expensive calculations,
    // implementing this method causes a massive speedup in this particular scenario by doing a shallow equals on the week data.
    shouldComponentUpdate(nextProps) {
        if (nextProps.currentWeek == this.propscurrentWeek && shallowArrayElementsEqual(this.props.weeks, nextProps.weeks)) {
            return false
        }
        return true
    }

    render() {
        const { currentWeek, weeks } = this.props
        var headingsA = []
        var headingsB = []
        var weekScoreboards = []
        var gameBodies = []
        var lastWeek


        //// FIRST LOOP: go through the weeks and put some information in place for headings and set some stuff up.
        weeks.forEach((sb) => {
            var scoreboard = new Scoreboard(sb)
            weekScoreboards.push(scoreboard)
            if (scoreboard.games.cap.actual !== null) {
                lastWeek = scoreboard
            }

            var classes = 'dayHead'
            if (sb.week == currentWeek) {
                classes += ' currentWeek'
            }

            headingsA.push(<th key={sb.week} colSpan="2" className={classes}>{sb.week}</th>)
            headingsB.push(<th key={scoreboard.key('P')} className="info">P</th>)
            headingsB.push(<th key={scoreboard.key('A')} className="">A</th>)
        })


        //// SECOND LOOP: because tables of this type go down games and across weeks, we need to
        ///  loop the games outside and weeks inside in order to 'stripe' through the weeks.
        GAME_KEYS.forEach((gameKey) => {
            var values = []  // This will alternate Promise, Actual, Promise, Actual elements.

            weekScoreboards.forEach((scoreboard) => {
                //var game = scoreboard.games[gameKey]
                var modelKey = scoreboard.meta.modelKey + `.games.${gameKey}`

                const promiseVal = (
                    <Field model={modelKey+'.promise'}>
                        <input type="text" disabled={!scoreboard.meta.canEditPromise} className="form-field" autoComplete="off" />
                    </Field>
                )

                const actualVal = (
                    <Field model={modelKey+'.actual'}>
                        <input type="text" disabled={!scoreboard.meta.canEditActual} className="form-field" autoComplete="off" />
                    </Field>
                )

                values.push(
                    <td key={scoreboard.key('p')} className="info">{promiseVal}</td>,
                    <td key={scoreboard.key('a')} className="actual">{actualVal}</td>
                )
            })

            if (lastWeek) {
                var game = lastWeek.games[gameKey]
                values.push(
                    <td key="pct" className="pct">{game.percent()}%</td>,
                    <td key="points">{game.points()}</td>
                )
            } else {
                values.push(<td key="pct"></td>, <td key="points"></td>)
            }

            gameBodies.push(
                <tr key={gameKey}>
                    <th>{gameKey.toUpperCase()}</th>
                    {values}
                </tr>
            )
        })

        return (
            <table className="table table-condensed table-bordered table-hover submissionCenterStats">
                <thead>
                    <tr>
                        <th rowSpan="2">&nbsp;</th>
                        {headingsA}
                        <th colSpan="2"></th>
                    </tr>
                    <tr>
                        {headingsB}
                        <th>%</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    {gameBodies}
                </tbody>
            </table>
        )
    }
}

function rowSplit(scoreboards, handler) {
    var currentRow = []
    var rowId = 0
    scoreboards.forEach((sb) => {
        currentRow.push(sb)
        if (sb.meta.isClassroom) {
            handler(currentRow, rowId++)
            currentRow = []
        }
    })
    if (currentRow) {
        handler(currentRow, rowId++)
    }
}


const mapStateToProps = (state) => {
    // Ignore scoreboardsForm updates in props by clobbering it with null, until we actually need it in a component (more speedups)
    return objectAssign({}, state.submission.scoreboard, {scoreboardsForm: null})
}

export default connect(mapStateToProps)(SubmissionScoreboard)
