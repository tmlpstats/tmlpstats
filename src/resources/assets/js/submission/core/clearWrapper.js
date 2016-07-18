
// This is a diabolically simple reducer wrapper to help clear submission data
//
// The actual work is handled by the combineReducers reducer, but when the specific
// action 'submission.setReportingDate' comes through, we revert all submission state
// to default except for the "core" state
export default function clearWrapper(realReducer, onClear) {
    return  function() {
        if (arguments.length > 1 && arguments[1].type == 'submission.setReportingDate'){
            return onClear.apply(this, arguments)
        }
        return realReducer.apply(this, arguments)
    }
}
