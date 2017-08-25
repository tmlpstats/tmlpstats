import PropTypes from 'prop-types'
import React from 'react'

import { RegionSystemMessages } from '../reusable/system-messages/connected'

const SECTION = 'home'

export class HomeHeader extends React.PureComponent {
    static propTypes = {
        params: PropTypes.shape({
            regionAbbr: PropTypes.string
        })
    }

    render() {
        const { params } = this.props

        return <RegionSystemMessages region={params.regionAbbr} section={SECTION} />
    }
}
