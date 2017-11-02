import Immutable from 'immutable'
import moment from 'moment'
import { createSelector, defaultMemoize } from 'reselect'

import { objectAssign } from '../../reusable/ponyfill'

const emptyOrderedMap = Immutable.OrderedMap()
const unknownQuarter = {year: 1900, t1Distinction: 'unknown'}

export const joinedRegionQuarters = createSelector(
    (state, regionAbbr) => state.admin.regions.regions.data[regionAbbr],
    (state) => state.admin.lookups.quarters.data,
    (region, quarters) => {
        if (!region || !region.quarters) {
            return emptyOrderedMap
        }

        // Build a mapping of year+quarter so you can figure out completionQuarter
        let qYear = {}
        for (let k in quarters) {
            const quarter = quarters[k]
            qYear[quarterYearKey(quarter.quarterNumber, quarter.year)] = `${region.id}/${quarter.id}`
        }

        // Now build our iteration candidate
        return Immutable.OrderedMap().withMutations((m) => {
            region.quarters.forEach((rqId) => {
                const rq = region.regionQuarters[rqId]
                const quarter = (rq && rq.quarterId) ? quarters[rq.quarterId] : unknownQuarter
                const completionQuarterId = qYear[quarterYearKey(quarter.quarterNumber, quarter.year + 1)]
                const isCurrent = (region.currentQuarter == rqId)
                m.set(rq.id, objectAssign({ quarter, isCurrent, completionQuarterId }, rq))
            })
        })
    }
)

export const getQuarterTransitions = defaultMemoize((regionQuarter) => {
    return {
        [regionQuarter.classroom1Date]: true,
        [regionQuarter.classroom2Date]: true,
        [regionQuarter.classroom3Date]: true
    }
})

export const quarterReportingDates = createSelector(
    (q) => q.firstWeekDate,
    (q) => q.endWeekendDate,
    (firstWeekDate, endWeekendDate) => {
        let output = []
        if (firstWeekDate && endWeekendDate) {
            const end = moment(endWeekendDate)
            let current = moment(firstWeekDate)
            let sanityCounter = 50 // Sanity check

            while (!current.isAfter(end) && --sanityCounter > 0) {
                output.push(current)
                current = current.clone().add(1, 'week')
            }
        }
        return output
    }
)

export const annotatedRegionQuarter = defaultMemoize((regionQuarter) => {
    const reportingDates = quarterReportingDates(regionQuarter)
    const transitions = getQuarterTransitions(regionQuarter)

    const annotatedDates = Immutable.OrderedMap().withMutations((m) => {
        reportingDates.forEach(date => {
            const dateString = date.format('YYYY-MM-DD')
            const isMilestone = transitions[dateString] || false
            m.set(dateString, {date, dateString, isMilestone})
        })
    })

    return objectAssign({}, regionQuarter, {
        reportingDates: reportingDates,
        transitions: transitions,
        annotatedDates: annotatedDates
    })
})

function quarterYearKey(quarterNumber, year) {
    return `${quarterNumber}.${year}`
}
