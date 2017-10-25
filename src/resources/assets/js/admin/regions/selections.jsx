import React from 'react'
import { Link, withRouter } from 'react-router'
import { connectRedux, rebind } from '../../reusable/dispatch'

import { checkQuartersData } from './checkers'
import { joinedRegionQuarters } from './selectors'
import RegionBase from './RegionBase'

@withRouter
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

    componentWillMount() {
        rebind(this, 'setQuarter', 'getLabel')
    }

    getLabel(cq) {
        return `${cq.quarter.t1Distinction} ${cq.quarter.year} (starting ${cq.startWeekendDate})`
    }

    checkData() {
        if (!checkQuartersData(this) || !this.checkRegions()) {
            return false
        }

        if (!this.props.params.quarterId) {
            let currentQuarterId = this.props.regionQuarters.find(q => q.isCurrent).quarterId
            setTimeout(() => this.props.router.push(this.regionQuarterBaseUri(currentQuarterId)))
            return false
        }

        return true
    }

    setQuarter(event) {
        this.props.router.push(this.regionQuarterBaseUri(event.target.value))
    }

    render() {
        if (!this.checkData()) {
            return <div>Loading...</div>
        }

        const { quarterId, regionAbbr } = this.props.params
        const quarter = this.props.quarters.data[quarterId]

        let options = []
        this.props.regionQuarters.forEach((q) => {
            options.push(
                <option key={q.id} value={q.quarterId}>{this.getLabel(q)}</option>
            )
        })

        const regionId = this.props.regions.data[regionAbbr].id
        const base = this.regionQuarterBaseUri()
        return (
            <div>
                Select quarter
                <form className="form-horizontal">
                    <select value={quarterId} onChange={this.setQuarter}>
                        {options}
                    </select>
                </form>
                <div>
                    <h3>Region: {regionAbbr} | Quarter: {quarter.t1Distinction} {quarter.year}</h3>
                </div>
                <div>
                    <ul>
                        <li><Link to={base+'/quarter_dates'}>Quarter Dates</Link></li>
                        <li><Link to={base+'/manage_scoreboards'}>Manage Scoreboard Locks</Link></li>
                        <li><Link to={base+'/accountability_rosters'}>Next Qtr Weekend Accountability Rosters</Link></li>
                        <li><a href={'/regions/'+regionId}>Old Admin Page</a></li>
                    </ul>
                </div>
                {this.props.children}
            </div>
        )
    }
}
