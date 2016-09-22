import { normalize } from 'normalizr'

import { coreInit, centerQuarterData, cqResponse } from './data'
import { objectAssign } from '../../reusable/ponyfill'
import { delayDispatch } from '../../reusable/dispatch'
import Api from '../../api'

const { moment } = window

export const initState = coreInit.actionCreator()

export function initSubmission(centerId, reportingDate) {
    return (dispatch) => {
        dispatch(initState('loading'))
        return Api.SubmissionCore.initSubmission({
            center: centerId,
            reportingDate: reportingDate
        }).then((data) => {
            // We delay this dispatch because the default case causes a lot of downstream rendering.
            // The downstream rendering causes the error catch for this Promise to trigger.
            delayDispatch(dispatch, setSubmissionLookups(data, reportingDate))
        }).catch((err) => {
            dispatch(initState({error: err.error || err}))
        })
    }
}

export function setSubmissionLookups(data, reportingDate) {
    return (dispatch) => {
        const lookups = objectAssign({}, data.lookups)

        // yuck, but works for now while we're remapping
        const n = normalize(data, cqResponse)
        const c = n.entities.c[n.result]
        lookups.validRegQuarters = c.validRegQuarters
        lookups.accountabilities = n.entities.accountabilities
        lookups.currentQuarter = c.currentQuarter
        lookups.orderedAccountabilities = c.accountabilities  // canonically sorted accountabilities
        dispatch(centerQuarterData.replaceItems(n.entities.quarters))
        lookups.pastClassroom = {}

        /// Precompute items like pastClassroom based on quarter dates.
        if (data.currentQuarter) {
            reportingDate = moment(reportingDate)
            for (let i = 1; i <= 3; i++) {
                const crdate = moment(data.currentQuarter[`classroom${i}Date`])
                lookups.pastClassroom[i] = !crdate.isAfter(reportingDate)
            }
        }

        dispatch({
            type: 'core/setSubmissionLookups',
            payload: lookups
        })
        dispatch(initState('loaded'))
    }
}

export function setReportingDate(reportingDate) {
    return {
        type: 'submission.setReportingDate',
        payload: reportingDate
    }
}
