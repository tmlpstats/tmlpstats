import React from 'react'
import { delayDispatch } from '../../reusable/dispatch'

import * as actions from './actions'

/**
 * RegionBase implements some helpers for routes based on a single region (with regionAbbr in the path param)
 */
export default class RegionBase extends React.Component {
    checkRegions() {
        const { regionAbbr } = this.props.params
        const { loadState, data } = this.props.regions
        if (!data[regionAbbr] && loadState.available) {
            delayDispatch(this, actions.loadRegionsData(regionAbbr))
            return false
        }
        return loadState.loaded
    }

    regionCenters() {
        const {regionAbbr} = this.props.params
        const region = this.props.regions.data[regionAbbr]
        const centers = region.centers.map((centerId) => this.props.centers.data[centerId])
        return { region, centers }
    }

    regionsBaseUri() {
        return regionsBaseUri()
    }

    regionBaseUri() {
        return regionBaseUri(this)
    }

    regionQuarterBaseUri() {
        return regionQuarterBaseUri(this)
    }
}

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
