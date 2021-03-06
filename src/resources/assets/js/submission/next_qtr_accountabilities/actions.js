import { qtrAccountabilitiesData } from './data'
import { Promise, objectAssign } from '../../reusable/ponyfill'
import { getErrMessage } from '../../reusable/ajax_utils'
import { formActions } from '../../reusable/form_utils'


export function saveAccountabilityInfo(center, reportingDate, data) {
    return qtrAccountabilitiesData.runNetworkAction('save', {center, reportingDate, data}, {
        successHandler(_, { dispatch, getState }) {
            const original = getState().submission.next_qtr_accountabilities.data._original
            const newOriginal = objectAssign({}, original, {[data.id]: data}) // TODO consider an ImmutableJS collection
            dispatch(qtrAccountabilitiesData.replaceItem('_original', newOriginal))
            dispatch(formActions.setPristine(`${qtrAccountabilitiesData.opts.model}.${data.id}`))
        },
        failHandler(err) {
            data = objectAssign({}, data, {_err: getErrMessage(err)})
            return qtrAccountabilitiesData.replaceItem(data.id, data)
        }
    })
}

export function batchSaveAccountabilityInfo(center, reportingDate, toSave) {
    return (dispatch) => {
        return qtrAccountabilitiesData.runInGroup(dispatch, 'save', () => {
            const promises = toSave.map((item) => {
                return dispatch(saveAccountabilityInfo(center, reportingDate, item))
            })
            // Promise.all will await result of all the promises.
            return Promise.all(promises)
        })
    }
}
