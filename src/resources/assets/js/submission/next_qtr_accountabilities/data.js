import _ from 'lodash'

import Api from '../../api'
import FormReduxLoader from '../../reusable/redux_loader/rrf'
import { objectAssign } from '../../reusable/ponyfill'
import { formActions } from '../../reusable/form_utils'

import { repromisableAccountabilities } from './selectors'

export const qtrAccountabilitiesData = new FormReduxLoader({
    prefix: 'submission/next_qtr_accountabilities',
    model: 'submission.next_qtr_accountabilities.data',
    formReducer: true,
    messageManager: true,
    extraLMS: ['saveState'],
    actions: {
        load: {
            api: Api.Submission.NextQtrAccountability.allForCenter,
            transformData: (data, { getState }) => {
                let keyed = {}
                // first initialize with blank data for all possible accountabilities.
                repromisableAccountabilities(getState()).forEach((acc) => {
                    keyed[acc.id] = {id: acc.id}
                })

                // override anything we just got back from the service
                objectAssign(keyed, _.keyby(data, 'id'))

                // Keep a pointer at the original value so we can use it later for comparison.
                return objectAssign({_original: keyed}, keyed)
            },
            successHandler: (data, { loader, dispatch }) => {
                dispatch(loader.replaceCollection(data))
                dispatch(formActions.setPristine(loader.opts.model))
            }
        },
        save: {
            api: Api.Submission.NextQtrAccountability.stash,
            setLoaded: true
        },
    },
    setLoaded: true,
})
