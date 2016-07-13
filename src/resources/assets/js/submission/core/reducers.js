
export function coreReducer(state={}, action) {
    if (action) {
        switch (action.type) {
        case 'submission.setReportingDate':
            return Object.assign({}, state, {reportingDate: action.payload})
        }
    }
    return state
}
