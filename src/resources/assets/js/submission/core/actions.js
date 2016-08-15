import { coreInit } from './data'
import { bestErrorValue } from '../../reusable/ajax_utils'

export const initState = coreInit.actionCreator()

export function initSubmission(centerId, reportingDate) {
    return (dispatch, _, { Api }) => {
        dispatch(initState('loading'))
        return Api.SubmissionCore.initSubmission({
            center: centerId,
            reportingDate: reportingDate
        }).done((data) => {
            if (data.success) {
                dispatch(setSubmissionLookups(data.lookups))
            } else {
                dispatch(initState({error: data.error}))
            }
        }).fail((jqXHR, textStatus) => {
            dispatch(initState({error: bestErrorValue(jqXHR, textStatus)}))
        })
    }
}

export function setSubmissionLookups(lookups) {
    return (dispatch) => {
        dispatch(initState('loaded'))
        dispatch({
            type: 'core/setSubmissionLookups',
            payload: lookups
        })
    }
}

export function setReportingDate(reportingDate) {
    return {
        type: 'submission.setReportingDate',
        payload: reportingDate
    }
}
