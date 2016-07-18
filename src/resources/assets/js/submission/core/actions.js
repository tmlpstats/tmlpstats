import { coreInit } from './data'

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
            }
        }).fail(() => {
            dispatch(initState('failed'))
        })
    }
}

export function setSubmissionLookups(lookups) {
    return (dispatch) => {
        dispatch(initState('success'))
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
