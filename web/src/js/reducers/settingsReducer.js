import * as types from 'actions/actionTypes';
import initialState from 'store/initialState';

/**
 * Settings reducer
 *
 * state = {
 *    user: {
 *      showNotices: true
 *    },
 *    site: {},
 *    room: {}
 * }
 *
 * @param {*} state
 * @param {*} action
 * @returns {*}
 */
export default function settingsReducer(state = initialState.settings, action = {}) {
  switch (action.type) {
    case types.SETTINGS_ALL:
      return Object.assign({}, state, action.settings);
    case types.SETTINGS_USER:
      return Object.assign({}, state, {
        user: action.settings
      });
    case types.SETTINGS_SITE:
      return Object.assign({}, state, {
        site: action.settings
      });
    case types.SETTINGS_ROOM:
      return Object.assign({}, state, {
        room: action.settings
      });
    case types.SETTINGS_SOCKET:
      return Object.assign({}, state, {
        socket: action.settings
      });
    default:
      return state;
  }
}
