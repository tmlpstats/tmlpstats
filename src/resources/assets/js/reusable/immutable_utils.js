import { createSelectorCreator, defaultMemoize } from 'reselect'
import Immutable from 'immutable'

// create a "selector creator" that uses lodash.isEqual instead of ===
export const createImmutableSelector = createSelectorCreator(
  defaultMemoize,
  Immutable.is
)

export const createImmutableMemoize = () => createImmutableSelector(
    x => x,
    x => x
)
