import _ from 'lodash'

import SimpleReduxLoader from '../reusable/redux_loader/simple'
import Api from '../api'

export const quartersData = new SimpleReduxLoader({
    prefix: 'admin/quarters',
    actions: {
        load: {
            api: Api.Admin.Quarter.filter,
            setLoaded: true,
            transformData(data) {
                let output = _.keyBy(data, 'id')
                output.ordered = _.map(data, 'id')
                return output
            }
        }
    }
})
