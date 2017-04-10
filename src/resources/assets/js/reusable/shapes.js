import { PropTypes } from 'react'
/// Some React PropTypes shapes

export const loadStateShape = PropTypes.shape({
    state: PropTypes.string.isRequired,
    loaded: PropTypes.bool.isRequired,
    available: PropTypes.bool
})
