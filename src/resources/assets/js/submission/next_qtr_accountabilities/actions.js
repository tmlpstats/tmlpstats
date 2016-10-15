import { qtrAccountabilitiesData } from './data'

export function saveAccountabilityInfo(center, reportingDate, data) {
    return qtrAccountabilitiesData.runNetworkAction('save', {center, reportingDate, data}, {
        successHandler() {
            // We just need to clobber the default success handler, we don't necessarily have anything we need here.
        }
    })
}
