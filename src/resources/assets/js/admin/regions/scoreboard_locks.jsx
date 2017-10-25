import React from 'react'
import PropTypes from 'prop-types'

import { Link, withRouter } from 'react-router'

import { delayDispatch, connectRedux, rebind } from '../../reusable/dispatch'
import { Form, formActions } from '../../reusable/form_utils'
import { FormTypeahead } from '../../reusable/typeahead'
import { ButtonStateFlip, Panel, Alert } from '../../reusable/ui_basic'

import RegionBase from './RegionBase'
import * as actions from './actions'
import { getQuarterTransitions } from './selectors'

const mapStateToProps = (state) => state.admin.regions
const MODEL = 'admin.regions.scoreboardLock.data'

@connectRedux(mapStateToProps)
export class RegionScoreboards extends RegionBase {
    render() {
        if (!this.checkRegions()) {
            return <div>Loading...</div>
        }
        const baseUri = this.regionQuarterBaseUri()
        const { region, centers } = this.regionCenters()

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
        const { centers: otherCenters, regionQuarter } = this.regionCenters()
        const center = this.props.centers.data[centerId]
        const sbl = this.props.scoreboardLock.data
        const transitionDates = getQuarterTransitions(regionQuarter)

        let currentRow = []
        let outerRows = []
        outerRows.push(<div key={0}>{currentRow}</div>)
        sbl.weeks.forEach((weekData, idx) => {
            currentRow.push(
                <div key={idx} className="btn-toolbar">
                    <div className="btn-group"><h4>{weekData.week}</h4></div>
                    <LockButtons value={weekData.editPromise} week={idx} dispatch={dispatch} />
                </div>
            )
            if (transitionDates[weekData.week]) {
                currentRow = []
                outerRows.push(<div key={idx}>{currentRow}</div>)
            }
        })

        const weeksInfo = outerRows.map((v, idx) => {
            return (
                <div key={idx} className="col-md-8 col-lg-6">
                    <Panel>{v}</Panel>
                </div>
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
                <div className="row">
                    {weeksInfo}
                </div>

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


class LockButtons extends React.PureComponent {
    static propTypes = {
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
        this.props.dispatch(formActions.change(`${MODEL}.weeks[${this.props.week}].editPromise`, v))
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
