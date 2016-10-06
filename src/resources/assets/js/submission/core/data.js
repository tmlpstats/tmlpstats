import { Schema, arrayOf } from 'normalizr'

import { LoadingMultiState } from '../../reusable/reducers'
import SimpleReduxLoader from '../../reusable/redux_loader/simple'
import Api from '../../api'

export const SET_REPORTING_DATE = 'submission.setReportingDate'

export const coreInit = new LoadingMultiState('core/coreInit')


class CenterQuarterData extends SimpleReduxLoader {
    constructor() {
        super({
            prefix: 'submission/centerQuarters',
            loader: Api.LocalReport.getCenterQuarter,
            setLoaded: true,
            successHandler(data, {loader}) {
                return loader.replaceItem(data.quarterId, data)
            }
        })
    }

    getLabel(cq) {
        return `${cq.quarter.t1Distinction} ${cq.quarter.year} (starting ${cq.startWeekendDate})`
    }
}

export const centerQuarterData = new CenterQuarterData()


/// Some schema definitions


const CenterQuarter = new Schema('quarters', {idAttribute: 'quarterId'})
const Accountability = new Schema('accountabilities')

export const cqResponse = new Schema('c')
cqResponse.define({
    validRegQuarters: arrayOf(CenterQuarter),
    accountabilities: arrayOf(Accountability),
    currentQuarter: CenterQuarter
})
