import _ from 'lodash'

import Api from '../../api'
import FormReduxLoader from '../../reusable/redux_loader/rrf'

export const qtrAccountabilitiesData = new FormReduxLoader({
    prefix: 'submission/next_qtr_accountabilities',
    model: 'submission.next_qtr_accountabilities.data',
    formReducer: true,
    extraLMS: ['saveState'],
    actions: {
        load: {
            api: Api.Submission.NextQtrAccountability.allForCenter,
            transformData: (data) => {
                return _.keyby(data, 'id')
            }
        },
        save: {
            api: Api.Submission.NextQtrAccountability.stash,
        },
    },
    setLoaded: true,
})
