import React from 'react'
import PropTypes from 'prop-types'
import Immutable from 'immutable'

import { Link, withRouter } from 'react-router'

import { defaultMemoize } from 'reselect'
import { objectAssign } from '../../reusable/ponyfill'
import { delayDispatch, connectRedux, rebind } from '../../reusable/dispatch'
import { Form, formActions } from '../../reusable/form_utils'
import { FormTypeahead } from '../../reusable/typeahead'
import { ButtonStateFlip, Panel, Alert, ModeSelectButtons } from '../../reusable/ui_basic'

import RegionBase from './RegionBase'
import * as actions from './actions'
import { annotatedRegionQuarter } from './selectors'

const mapStateToProps = (state) => state.admin.regions
const MODEL = 'admin.regions.scoreboardLock.data'

@connectRedux(mapStateToProps)
export class RegionScoreboards extends RegionBase {
    render() {
        if (!this.checkRegions()) {
            return <div>Loading...</div>
        }
        const baseUri = this.regionQuarterBaseUri()
        const { centers } = this.regionCenters()

        const dispCenters = centers.map((center) => {
            const href = `${baseUri}/manage_scoreboards/from/${center.abbreviation}`
            return <div key={center.id}><Link to={href}>{center.name}</Link></div>
        })
        return (
            <div>
                <h4>Please select a center:</h4>
                {dispCenters}
            </div>
        )
    }
}

@withRouter
@connectRedux(mapStateToProps)
export class EditScoreboardLock extends RegionBase {
    constructor(props) {
        super(props)
        rebind(this, 'onSubmit', 'onSelectAll')
    }
    checkLock() {
        const { centerId, quarterId } = this.props.params
        const { data, loadState } = this.props.scoreboardLock
        if ((!data || data.centerId != centerId) && loadState.available) {
            const { region } = this.regionCenters()
            if (region) {
                delayDispatch(this, actions.loadScoreboardLockData(centerId, quarterId))
            }
            return false
        }
        return loadState.loaded
    }

    render() {
        if (!this.checkRegions() || !this.checkLock()) {
            return <div>Loading...</div>
        }
        const { params, dispatch } = this.props
        const { centerId } = params
        const { centers: otherCenters, regionQuarter: rawRegionQuarter } = this.regionCenters()
        const center = this.props.centers.data[centerId]
        const sbl = this.props.scoreboardLock.data
        const regionQuarter = annotatedRegionQuarter(rawRegionQuarter)

        // Sanity check until we provide a reporting date management UI
        if (!regionQuarter.reportingDates.length) {
            return (
                <Alert alert="danger">
                    No reporting dates configured for this quarter! Check quarter setup,
                    or contact Future Stats Team.
                    <p><b>Region:</b> {params.regionAbbr}</p>
                    <p><b>Quarter:</b> {params.quarterId}</p>
                </Alert>
            )
        }

        const allLocks = []
        getLocksByDates(regionQuarter, sbl).forEach(({date, dateString, idx, rdLocks}) => {
            allLocks.push(
                <ReportingDateLocks
                    key={dateString} model={`${MODEL}.reportingDates[${idx}]`}
                    regionQuarter={regionQuarter}
                    reportingDate={date} sbl={rdLocks}
                    dispatch={dispatch} />
            )
        })

        const otherCenter = sbl.applyCenter.length != 1 || sbl.applyCenter[0] != centerId

        let acWarn
        if (otherCenter) {
            acWarn = (
                <div className="col-md-5">
                <Alert alert="warning" icon="warning-sign">
                    Applying to a different center or more than one center
                    copies these locks to those center(s), overwriting
                    what was there.
                </Alert>
                </div>
            )
        }

        return (
            <Form model={MODEL} onSubmit={this.onSubmit.bind(this)}>
                <h2>Edit Scoreboard Locks - {otherCenter? 'Multiple Centers' : center.name}</h2>
                <Alert alert="warning">
                    <b>Important</b> - The way this dialogue works has changed since the last time
                    this was used.

                    <p>In the past, you set the locks at the beginning of the repromise week and then un-set them.</p>
                    <p>Now, promise locks are set for a reporting week, this should reduce the incidence of issues.</p>
                    <p>We are going to keep changing this user experience to make it more streamlined, this is currently done as-is for the Nov 2017 repromise.</p>
                    <p>Contact Future Stats team if you need more info.</p>
                </Alert>
                {allLocks}
                <div className="row">
                    <div className="form-group">
                        <div className="col-md-2">
                            <label>Apply to center(s)</label>
                            <br />
                            <button type="button" className="btn btn-default" onClick={this.onSelectAll}>Select All Centers</button>
                        </div>
                        <div className="col-md-5">
                            <FormTypeahead
                                    model={MODEL+'.applyCenter'} items={otherCenters}
                                    keyProp="abbreviation" labelProp="name"
                                    multiple={true} rows={8} />
                        </div>
                        {acWarn}
                    </div>
                </div>

                <ButtonStateFlip buttonClass="btn btn-primary btn-lg"
                                 loadState={this.props.scoreboardLock.saveState}
                                 wrapGroup={true}>Save</ButtonStateFlip>
                <div style={{paddingTop: '20em'}}>&nbsp;</div>
            </Form>
        )
    }

    onSelectAll() {
        this.props.dispatch(formActions.change(`${MODEL}.applyCenter`, this.regionCenters().centers.map(x => x.abbreviation)))
    }

    onSubmit(data) {
        this.props.dispatch(actions.saveScoreboardLocks(data.applyCenter[0], data.quarterId, data)).then(() => {
            const applyCenter = data.applyCenter.slice(1)
            if (applyCenter.length > 0) {
                this.props.dispatch(formActions.change(`${MODEL}.applyCenter`, applyCenter))
                setTimeout(() => { this.onSubmit({...data, applyCenter}) }, 200)
            } else {
                this.props.router.push(this.regionQuarterBaseUri() + '/manage_scoreboards')
            }
        })
    }
}

// This helper is a selector which co-locates locks with dates.
// It's needed because the 'reportingDates' array is a positional array, not keyed.
// We're doing the positional array to make it easier to transition to GraphQL based objects later.
const getLocksByDates = defaultMemoize(function(regionQuarter, sbl) {
    // avoid a N^2 loop by keying the locks first.
    const keyedLocks = Immutable.Map(Immutable.Seq(sbl.reportingDates).map((x, idx) => {
        return [x.reportingDate, {idx: idx, rdLocks: x}]
    }))

    return regionQuarter.annotatedDates.map((qdate) => {
        const v = keyedLocks.get(qdate.dateString) || {}
        return objectAssign(v, qdate)
    })
})


const LOCK_OPTIONS = [
    {key: 'all_locked', label: 'All Locked'},
    {key: 'customize', label: 'Customize'},
]


class ReportingDateLocks extends React.PureComponent {
    static propTypes = {
        reportingDate: PropTypes.object.isRequired,
        regionQuarter: PropTypes.object.isRequired,
        model: PropTypes.string.isRequired,
        dispatch: PropTypes.func.isRequired,
        sbl: PropTypes.shape({
            reportingDate: PropTypes.string,
            weeks: PropTypes.array,
        })
    }

    constructor(props) {
        super(props)
        rebind(this, 'changeMode')
    }

    changeMode(mode) {
        const weekString = this.props.reportingDate.format('YYYY-MM-DD')
        switch(mode){
        case 'all_locked':
            this.props.dispatch(actions.fullyLockWeek(weekString))
            break
        case 'customize':
            this.props.dispatch(actions.unlockWeek(weekString, this.props.regionQuarter.reportingDates))
            break
        }
    }

    render() {
        const { sbl, regionQuarter, dispatch } = this.props

        let current = 'all_locked'
        let weeksInfo
        if (sbl && sbl.weeks && sbl.weeks.length) {
            current = 'customize'
            let currentRow = []
            let outerRows = []
            outerRows.push(<div key={0}>{currentRow}</div>)
            sbl.weeks.forEach((weekData, idx) => {
                currentRow.push(
                    <div key={idx} className="btn-toolbar">
                        <div className="btn-group"><h4>{weekData.week}</h4></div>
                        <LockButtons model={`${this.props.model}.weeks[${idx}]`} value={weekData.editPromise} week={idx} dispatch={dispatch} />
                    </div>
                )
                const aWeek = regionQuarter.annotatedDates.get(weekData.week)
                if (aWeek && aWeek.isMilestone) {
                    currentRow = []
                    outerRows.push(<div key={idx}>{currentRow}</div>)
                }
            })

            weeksInfo = outerRows.map((v, idx) => {
                return (
                    <div key={idx} className="col-md-8 col-lg-6">
                        <Panel>{v}</Panel>
                    </div>
                )
            })
        }


        return (
            <div>
            <h4>On Date: {this.props.reportingDate.format('YYYY-MM-DD')}</h4>
            <ModeSelectButtons items={LOCK_OPTIONS} onClick={this.changeMode} current={current} />
            <div className="row">
                {weeksInfo}
            </div>
            </div>
        )
    }
}


class LockButtons extends React.PureComponent {
    static propTypes = {
        model: PropTypes.string.isRequired,
        week: PropTypes.number.isRequired,
        value: PropTypes.bool
    }

    constructor(props) {
        super(props)
        rebind(this, 'onLock', 'onUnlock')
    }

    render() {
        const { props } = this
        const p1 = props.value? buttonOffProps : buttonChosenProps
        const p2 = props.value? buttonChosenProps : buttonOffProps
        return (
            <div className="btn-group" role="radiogroup">
                <button role="radio" onClick={this.onLock} {...p1}><span className="glyphicon glyphicon-lock"></span> Lock Promise</button>
                <button role="radio" onClick={this.onUnlock} {...p2}>Editable</button>
            </div>
        )
    }

    setValue(v) {
        this.props.dispatch(formActions.change(`${this.props.model}.editPromise`, v))
        return false
    }

    onLock() {
        return this.setValue(false)
    }

    onUnlock() {
        return this.setValue(true)
    }
}
const buttonChosenProps = {
    className: 'btn btn-default active',
    'aria-checked': 'true',
    type: 'button'
}
const buttonOffProps = {
    className: 'btn btn-default',
    'aria-checked': 'false',
    type: 'button'
}
