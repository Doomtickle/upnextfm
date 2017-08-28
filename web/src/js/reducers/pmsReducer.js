import * as types from 'actions/actionTypes';
import initialState from 'store/initialState';
import store from 'store/store';
import { sanitizeMessage } from 'utils/messages';

/**
 * Adds a pm to the user's conversations
 *
 * @param {*} state
 * @param {*} action
 * @returns {*}
 */
function receive(state, action) {
  const msg           = sanitizeMessage(action.message);
  const key           = msg.from.toLowerCase();
  const activeChat    = store.getState().layout.activeChat;
  const conversations = Object.assign({}, state.conversations);

  if (conversations[key] === undefined) {
    conversations[key] = {
      messages:       [msg],
      numNewMessages: 1
    };
  } else {
    conversations[key].messages.push(msg);
    if (activeChat !== key) {
      conversations[key].numNewMessages += 1;
    }
  }

  return Object.assign({}, state, {
    isSending: false,
    conversations
  });
}

/**
 * Adds a pm this user sent to another user
 *
 * @param {*} state
 * @param {*} action
 * @returns {*}
 */
function sent(state, action) {
  const msg           = sanitizeMessage(action.message);
  const key           = msg.to.toLowerCase();
  const conversations = Object.assign({}, state.conversations);

  if (conversations[key] === undefined) {
    conversations[key] = {
      messages:       [msg],
      numNewMessages: 0
    };
  } else {
    conversations[key].messages.push(msg);
  }

  return Object.assign({}, state, {
    isSending: false,
    conversations
  });
}

/**
 * Private messages reducer
 *
 * @param {*} state
 * @param {*} action
 * @returns {*}
 */
export default function pmsReducer(state = initialState.pms, action = {}) {
  switch (action.type) {
    case types.PMS_SUBSCRIBED:
      return Object.assign({}, state, {
        isSubscribed: true,
        isSending:    false
      });
    case types.PMS_SENT:
      return sent(state, action);
    case types.PMS_RECEIVE:
      return receive(state, action);
    case types.PMS_SENDING:
      return Object.assign({}, state, {
        isSending: true
      });
    default:
      return state;
  }
}
