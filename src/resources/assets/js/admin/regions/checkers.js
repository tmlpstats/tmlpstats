import { delayDispatch } from '../../reusable/dispatch'

import { quartersData } from '../data'
import * as actions from './actions'


export function checkRegionData(component, regionAbbr) {
    const { loadState, data } = component.props.regions
    if (!data[regionAbbr] && loadState.available) {
        delayDispatch(component, actions.loadRegionsData(regionAbbr))
        return false
    }
    return loadState.loaded
}

export function checkQuartersData(component) {
    const { quarters } = component.props
    if (!quarters.loadState.loaded) {
        if (quarters.loadState.available) {
            delayDispatch(component, quartersData.load())
        }
        return false
    }
    return true
}
