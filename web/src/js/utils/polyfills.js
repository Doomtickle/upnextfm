/* eslint-disable */
import Promise from 'promise-polyfill';
if (!window.Promise) {
  window.Promise = Promise;
}
import 'whatwg-fetch';
import injectTapEventPlugin from 'react-tap-event-plugin';

injectTapEventPlugin();
