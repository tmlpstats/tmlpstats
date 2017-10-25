import React from 'react'
import { createSelector } from 'reselect'

import { checkRegionData } from './checkers'

/**
 * RegionBase implements some helpers for routes based on a single region (with regionAbbr in the path param)
 */
export default class RegionBase extends React.Component {
    checkRegions() {
        const { regionAbbr } = this.props.params
        return checkRegionData(this, regionAbbr)
    }

    regionCenters() {
        return regionCenters(this.props)
    }

    regionsBaseUri() {
        return regionsBaseUri()
    }

    regionBaseUri() {
        return regionBaseUri(this)
    }

    regionQuarterBaseUri(quarterId=undefined) {
        return regionQuarterBaseUri(this, quarterId)
    }
}

const regionCenters = createSelector(
    (props) => props.regions.data[props.params.regionAbbr],
    (props) => props.centers,
    (props) => props.params.quarterId,
    (region, allCenters, quarterId) => {
        const regionQuarter = quarterId? region.regionQuarters[`${region.id}/${quarterId}`] : null
        const centers = region.centers.map((centerId) => allCenters.data[centerId])
        return { region, centers, regionQuarter }
    }
)

export function regionsBaseUri() {
    return '/admin/regions'
}

export function regionBaseUri(component) {
    const { regionAbbr } = component.props.params
    return `${regionsBaseUri()}/${regionAbbr}`
}

export function regionQuarterBaseUri(component, quarterId) {
    quarterId = quarterId || component.props.params.quarterId
    return `${regionBaseUri(component)}/quarter/${quarterId}`
}
