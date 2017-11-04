import PropTypes from 'prop-types'

export const loadStateShape = PropTypes.shape({
    state: PropTypes.string.isRequired,
    loaded: PropTypes.bool.isRequired,
    available: PropTypes.bool
})
