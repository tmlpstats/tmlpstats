import React from 'react'
import { Link, withRouter } from 'react-router'
import { delayDispatch, connectRedux } from '../../reusable/dispatch'
import { Form, Field, SimpleSelect } from '../../reusable/form_utils'
import { SubmitFlip } from '../../reusable/ui_basic'

import RegionBase from './RegionBase'
import * as actions from './actions'

const mapStateToProps = (state) => state.admin.regions

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
                <h4>{region.title}</h4>
                {dispCenters}
            </div>
        )
    }
}

@withRouter
@connectRedux(mapStateToProps)
export class EditScoreboardLock extends RegionBase {
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
        const { centerId } = this.props.params
        const { centers: otherCenters } = this.regionCenters()
        const center = this.props.centers.data[centerId]
        const sbl = this.props.scoreboardLock.data

        const MODEL = 'admin.regions.scoreboardLock.data'

        const weeksInfo = sbl.weeks.map((weekData, idx) => {
            const modelPrefix = `${MODEL}.weeks[${idx}]`
            return (
                <div key={idx}>
                    <h4>{weekData.week}</h4>
                    <Field model={modelPrefix + '.editPromise'}>
                        <label><input type="checkbox" /> Edit Promise</label>
                    </Field>
                </div>
            )
        })
        var acWarn
        if (centerId != sbl.applyCenter) {
            acWarn = (
                <div className="col-md-6 bg-warning">
                    Applying to a different center copies these locks to that center.
                    It will not save back to the center it came from.
                </div>
            )
        }

        return (
            <Form model={MODEL} onSubmit={this.onSubmit.bind(this)}>
                <h2>Edit Scoreboard Locks - {center.name}</h2>
                {weeksInfo}
                <br />
                <div className="form-group">
                    <label className="col-md-2">Apply to center</label>
                    <div className="col-md-4">
                        <SimpleSelect
                                model={MODEL+'.applyCenter'} items={otherCenters}
                                keyProp="abbreviation" labelProp="name" />
                    </div>
                    {acWarn}
                </div>
                <SubmitFlip loadState={this.props.scoreboardLock.saveState}>
                    Save
                </SubmitFlip>
            </Form>
        )
    }

    onSubmit(data) {
        this.props.dispatch(actions.saveScoreboardLocks(data.applyCenter, data.quarterId, data)).then(() => {
            this.props.router.push(this.regionQuarterBaseUri() + '/manage_scoreboards')
        })
    }
}
