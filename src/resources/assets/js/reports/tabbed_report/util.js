import _ from 'lodash'
import { createSelector } from 'reselect'

import { objectAssign } from '../../reusable/ponyfill'

/**
 * Filter reports based on given flags.
 *
 * Probably overkill, but more capability in the future as we go to more powerful code-genned reports.
 */
export function filterReportFlags(report, flags) {
    // Loop 1: add parent pointers to make the next step easier
    let flagged = []
    eachReportChild(report, (key, child) => {
        if (child.children) {
            child.children.forEach((subChildKey) => {
                report.children[subChildKey].parent = key
            })
        }
        if (child.requiredFlags && child.requiredFlags.length) {
            flagged.push(child)
        }
    })

    let newReport = objectAssign({}, report)

    // Loop 2: Remove any flagged reports
    flagged.forEach((child) => {
        let good = true
        child.requiredFlags.forEach((flag) => {
            if (!flags[flag]) {
                good = false
            }
        })
        if (!good) {
            delete newReport[child.id]
            const parentId = child.parent || '_root'
            newReport[parentId] = filterChildren(newReport[parentId], child.id)
        }
    })

    return newReport
}

function filterChildren(report, childId) {
    return objectAssign({}, report, {
        children: _.without(report.children, childId)
    })
}

function eachReportChild(report, callback) {
    for (let key in report.children) {
        callback(key, report.children[key])
    }
}
