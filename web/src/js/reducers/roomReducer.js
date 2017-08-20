import * as types from 'actions/actionTypes';
import initialState from 'store/initialState';

/**
 * Adds a user to the room users list
 *
 * @param {*} state
 * @param {{type: string, user: *}} action
 * @returns {*}
 */
function joined(state, action) {
  const username = action.user.username;
  const newState = Object.assign({}, state);
  if (newState.users.indexOf(username) === -1) {
    newState.users.push(username);
  }
  return newState;
}

/**
 * Removes a user from the room users list
 *
 * @param {*} state
 * @param {{type: string, username: string}} action
 * @returns {*}
 */
function parted(state, action) {
  const username = action.username;
  const newState = Object.assign({}, state);
  newState.users = newState.users.filter((un) => {
    return un !== username;
  });
  return newState;
}

/**
 * Sets all of the users in the room users list
 *
 * @param {*} state
 * @param {{type: string, users: Array}} action
 * @returns {*}
 */
function users(state, action) {
  const newUsers = [];
  action.users.forEach((username) => {
    newUsers.push(username);
  });
  return Object.assign({}, state, {
    users: newUsers
  });
}

/**
 * Sets the recent room messages
 *
 * @param {*} state
 * @param {{type: string, messages: Array}} action
 * @returns {*}
 */
function messages(state, action) {
  const newMessages = [];
  action.messages.forEach((message) => {
    message.date = new Date(message.date);
    newMessages.push(message);
  });
  return Object.assign({}, state, {
    messages: newMessages
  });
}

/**
 *
 * @param {*} state
 * @param {{type: string, payload: *}} action
 * @returns {*}
 */
function payload(state, action) {
  switch (action.payload.cmd) {
    case types.CMD_SEND:
      const messages = state.messages;
      const message  = action.payload.msg;
      message.date   = new Date(message.date);
      messages.push(message);
      return Object.assign({}, state, {
        messages
      });
      break;
    default:
      return state;
  }
}

/**
 * Rooms reducer
 *
 * @param {*} state
 * @param {*} action
 * @returns {*}
 */
export default function roomReducer(state = initialState.room, action = {}) {
  switch (action.type) {
    case types.ROOM_NAME:
      return Object.assign({}, state, {
        name: action.name
      });
    case types.ROOM_INPUT_CHANGE:
      return Object.assign({}, state, {
        inputValue: action.inputValue
      });
    case types.ROOM_SEND:
      return Object.assign({}, state, {
        inputValue: ''
      });
    case types.ROOM_TOGGLE_USERS_COLLAPSED:
      return Object.assign({}, state, {
        isUsersCollapsed: !state.isUsersCollapsed
      });
    case types.ROOM_JOINED:
      return joined(state, action);
    case types.ROOM_PARTED:
      return parted(state, action);
    case types.ROOM_USERS:
      return users(state, action);
    case types.ROOM_MESSAGES:
      return messages(state, action);
    case types.ROOM_PAYLOAD:
      return payload(state, action);
    default:
      return state;
  }
}
