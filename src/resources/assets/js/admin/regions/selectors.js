import { createSelector } from 'reselect'

import { objectAssign } from '../../reusable/ponyfill'

const emptyList = []
const unknownQuarter = {year: 1900, t1Distinction: 'unknown'}

export const joinedRegionQuarters = createSelector(
    (state, regionAbbr) => state.admin.regions.regions.data[regionAbbr],
    (state) => state.admin.lookups.quarters.data,
    (region, quarters) => {
        if (!region || !region.quarters) {
            return emptyList
        }
        return region.quarters.map((rqId) => {
            const rq = region.regionQuarters[rqId]
            const quarter = (rq && rq.quarterId) ? quarters[rq.quarterId] : unknownQuarter
            const isCurrent = (region.currentQuarter == rqId)
            return objectAssign({ quarter, isCurrent }, rq)
        })
    }
)
