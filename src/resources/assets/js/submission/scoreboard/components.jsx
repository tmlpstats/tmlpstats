import _ from 'lodash'
import React from 'react'
import { connect } from 'react-redux'
import { Form, Control } from 'react-redux-form'
import moment from 'moment'

import { shallowArrayElementsEqual } from '../../reusable/compare'
import { delayDispatch, rebind } from '../../reusable/dispatch'
import { NullableTextControl } from '../../reusable/form_utils'
import Scoreboard, { GAME_KEYS } from '../../reusable/scoreboard'
import { ButtonStateFlip, MessagesComponent } from '../../reusable/ui_basic'
import { objectAssign } from '../../reusable/ponyfill'

import { SubmissionBase } from '../base_components'
import { loadScoreboard, saveScoreboards } from './actions'
import { SCOREBOARDS_FORM_KEY } from './data'

import { getValidationMessagesIfStale } from '../review/actions'

/**
 * SubmissionScoreboard is the root component for rendering the scoreboard view.
 */
class SubmissionScoreboardView extends SubmissionBase {
    static onRouteEnter(nextState) {
        const { store } = require('../../store')
        store.dispatch(getValidationMessagesIfStale(nextState.params.centerId, nextState.params.reportingDate))
    }

    constructor(props) {
        super(props)
        rebind(this, 'checkToSave')
        this.debouncedCheckToSave = _.debounce(this.checkToSave, 600, {trailing: true, maxWait: 10000})
    }

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
        const { saving, toSave, scoreboards } = this.props
        if (saving.state == 'failed') {
            return // TODO, have some status loop and reap failed weeks
        }
        if (saving.state == 'loading') {
            setTimeout(this.checkToSave, 2000)
        } else if (toSave.items.size > 0) {
            const { centerId, reportingDate } = this.props.params
            delayDispatch(this, saveScoreboards(centerId, reportingDate, toSave.items, scoreboards))
        }
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        } else if (!this.props.scoreboards || !this.props.scoreboards.length) {
            // This should never happen, should it?
            return <div>No data!?!?</div>
        }
        this.debouncedCheckToSave()
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

        var allMessages = []
        for (var week in this.props.messages) {
            let messages = this.props.messages[week]
            let referenceString = 'for ' + moment(week).format('MMM D, YYYY')
            allMessages.push(<MessagesComponent key={week}
                                                messages={messages}
                                                referenceString={referenceString} />)
        }
        return (
            <Form model={SCOREBOARDS_FORM_KEY} onSubmit={this.checkToSave}>
                <h3>Scoreboard</h3>
                <div style={{overflow: 'scroll', maxHeight: '14em'}}>{allMessages}</div>
                <div>{rows}</div>
                <ButtonStateFlip loadState={this.props.saving} offset='col-sm-12' wrapGroup={true}>Save</ButtonStateFlip>
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
export class ScoreboardRow extends SubmissionBase {

    // Normally, we don't have to implement shouldComponentUpdate because connect handles a lot of this for us.
    // However, the week grouping causes props mismatches and this results in a full re-render of all four week boxes
    // regardless of whether data changed in that given week box. Since week boxes also include rather expensive calculations,
    // implementing this method causes a massive speedup in this particular scenario by doing a shallow equals on the week data.
    shouldComponentUpdate(nextProps) {
        if (nextProps.currentWeek == this.props.currentWeek && shallowArrayElementsEqual(this.props.weeks, nextProps.weeks)) {
            return false
        }
        return true
    }

    render() {
        const { currentWeek, weeks } = this.props
        let headingsA = []
        let headingsB = []
        let weekScoreboards = []
        let gameBodies = []
        let totalBodies = []
        let lastWeek

        //// FIRST LOOP: go through the weeks and put some information in place for headings and set some stuff up.
        weeks.forEach((sb) => {
            const scoreboard = new Scoreboard(sb)
            weekScoreboards.push(scoreboard)
            if (scoreboard.hasActuals()) {
                lastWeek = scoreboard
            }

            let classes = 'dayHead'
            if (sb.week == currentWeek) {
                classes += ' currentWeek'
            }

            headingsA.push(<th key={sb.week} colSpan="2" className={classes}>{moment(sb.week).format('MMM D, YYYY')}</th>)
            headingsB.push(
                <th key={scoreboard.key('P')} className="info">P</th>,
                <th key={scoreboard.key('A')} className="">A</th>
            )
        })


        //// SECOND LOOP: because tables of this type go down games and across weeks, we need to
        ///  loop the games outside and weeks inside in order to 'stripe' through the weeks.
        GAME_KEYS.forEach((gameKey) => {
            let values = []  // This will alternate Promise, Actual, Promise, Actual elements.

            weekScoreboards.forEach((scoreboard) => {
                //var game = scoreboard.games[gameKey]
                const modelKey = scoreboard.meta.modelKey + `.games.${gameKey}`

                const promiseVal = (
                    <NullableTextControl
                        model={modelKey+'.promise'} disabled={!scoreboard.meta.canEditPromise}
                        className=" " controlProps={{autoComplete: 'off'}} />
                )

                const actualVal = (
                    <NullableTextControl
                        model={modelKey+'.actual'} disabled={!scoreboard.meta.canEditActual}
                        className=" " controlProps={{autoComplete: 'off', }} />
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
        if (lastWeek) {
            totalBodies.push(
                <th key="totals_space" colSpan={weeks.length * 2 + 1}>{lastWeek.rating()}</th>,
                <th key="totals_pct" className="pct">{lastWeek.percent()}%</th>,
                <th key="totals_points">{lastWeek.points()}</th>
            )
        }

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
                <tfoot>
                    <tr>{totalBodies}</tr>
                </tfoot>
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

export default connect(mapStateToProps)(SubmissionScoreboardView)
