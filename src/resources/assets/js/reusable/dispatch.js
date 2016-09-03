export function delayDispatch(dcontext, ...actions) {
    if (dcontext.props) {
        dcontext = dcontext.props
    }
    if (dcontext.dispatch) {
        dcontext = dcontext.dispatch
    }
    setTimeout(() => {
        actions.forEach((action) => {
            dcontext(action)
        })
    })
}
