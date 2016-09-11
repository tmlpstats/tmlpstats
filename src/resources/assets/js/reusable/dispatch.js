/**
 * Do the common pattern of dispatching after zero delay.
 *
 * This is used often if some renderer causes a dispatch, because  dispatching is
 * normally not allowed during render. Also useful in event handlers so that the
 * event handler gets a chance to bubble out before dispatch begins.
 *
 * @param  func|object dcontext The dispatcher. Can be either `this` from a component,
 *                              `this.props`, or the dispatch function itself.
 * @param  ...object   actions  One or more actions to dispatch.
 */
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

/**
 * Rebind methods to 'this' - Usually used for event handlers that are going to be passed, like onClick and dispatch events.
 * @param  object     target  What to bind the methods to. Usually 'this' of some class.
 * @param  {...[type]} methods Methods to bind. Can be either string keys or the method objects themselves.
 */
export function rebind(target, ...methods) {
    methods.forEach((m) => {
        if (typeof m == 'string') {
            target[m] = target[m].bind(target)
        } else {
            target[m.name] = m.bind(target)
        }
    })
}
