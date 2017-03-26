import { delayDispatch } from '../../reusable/dispatch'
import { getValidationMessagesIfStale } from '../review/actions'

import * as actions from './actions'

export default function checkCoreData(centerId, reportingDate, core, dispatch) {
    if (reportingDate != core.reportingDate) {
        dispatch(actions.setReportingDate(reportingDate))
        return false
    } else if (core.coreInit.state == 'new') {
        delayDispatch(dispatch,
            actions.initSubmission(centerId, reportingDate),
            getValidationMessagesIfStale(centerId, reportingDate),
        )
        return false
    } else if (core.coreInit.state != 'loaded') {
        return false
    }
    return true
}
