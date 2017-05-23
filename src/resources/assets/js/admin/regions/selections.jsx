import React from 'react'
import { Link } from 'react-router'
import _ from 'lodash'

import { connectRedux, delayDispatch } from '../../reusable/dispatch'

import { quartersData } from '../data'
import { joinedRegionQuarters } from './selectors'
import RegionBase, { regionQuarterBaseUri } from './RegionBase'

@connectRedux()
export class SelectQuarter extends RegionBase {
    static mapStateToProps(state, ownProps) {
        const regionQuarters = joinedRegionQuarters(state, ownProps.params.regionAbbr)
        const { regions } = state.admin.regions
        return {
            quarters: state.admin.lookups.quarters,
            regionQuarters,
            regions,
        }
    }

    checkData() {
        if (!this.props.quarters.loadState.loaded) {
            if (this.props.quarters.loadState.available) {
                delayDispatch(this, quartersData.load())
            }
            return false
        }
        return this.checkRegions()
    }

    render() {
        if (!this.checkData()) {
            return <div>Loading...</div>
        }
        const { quarterId, regionAbbr } = this.props.params

        if (quarterId) {
            const quarter = this.props.quarters.data[quarterId]
            return (
                <div>
                    <h3>Region: {regionAbbr} | Quarter: {quarter.t1Distinction} {quarter.year}</h3>
                    {this.props.children}
                </div>
            )
        } else {
            var quarterChoices = this.props.regionQuarters.map((q) => {
                const target = regionQuarterBaseUri(this, q.quarterId)
                const style = q.isCurrent? {fontWeight: 'bold'} : {}
                return (
                    <li key={q.id}>
                        <span style={style}><Link to={target}>{q.quarter.t1Distinction} {q.quarter.year}</Link></span>
                        &nbsp;{q.startWeekendDate} to {q.endWeekendDate}
                    </li>
                )
            })
            return (
                <div>
                    Select quarter
                    <ul>{quarterChoices}</ul>
                </div>
            )
        }
    }
}

export class RegionQuarterIndex extends RegionBase {
    render() {
        const base = this.regionQuarterBaseUri()
        return (
            <div>
                <ul>
                    <li><Link to={base+'/quarter_dates'}>Quarter Dates</Link></li>
                    <li><Link to={base+'/manage_scoreboards'}>Manage Scoreboard Locks</Link></li>
                    <li><Link to={base+'/accountability_rosters'}>Next Qtr Weekend Accountability Rosters</Link></li>
                </ul>
            </div>
        )
    }
}
