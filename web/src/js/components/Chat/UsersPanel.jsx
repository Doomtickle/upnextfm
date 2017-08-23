import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { layoutToggleUsersCollapsed } from 'actions/layoutActions';
import { usersFindByUsername } from 'utils/users';
import Hidden from 'material-ui/Hidden';
import List, { ListItem } from 'material-ui/List';
import IconButton from 'material-ui/IconButton';
import KeyboardArrowLeft from 'material-ui-icons/KeyboardArrowLeft';
import User from 'components/Chat/User';

class UsersPanel extends React.Component {
  static propTypes = {
    room:   PropTypes.object,
    users:  PropTypes.object,
    layout: PropTypes.object
  };

  handleClickCollapse = () => {
    this.props.dispatch(layoutToggleUsersCollapsed());
  };

  render() {
    const { room, users, layout } = this.props;

    return (
      <div className={classNames(
        'up-room-panel__users',
        {
          'up-collapsed': layout.isUsersCollapsed
        }
      )}
      >
        <List>
          {room.users.map(username => (
            <ListItem key={username} button>
              <User user={usersFindByUsername(users.repo, username)} />
            </ListItem>
          ))}
        </List>
        <Hidden xsDown>
          <div className="up-room-users__controls">
            <IconButton className="up-collapse" onClick={this.handleClickCollapse}>
              <KeyboardArrowLeft className={classNames(
                'up-collapse__icon',
                {
                  'up-collapsed': layout.isUsersCollapsed
                }
               )}
              />
            </IconButton>
          </div>
        </Hidden>
      </div>
    );
  }
}

function mapStateToProps(state) {
  return {
    room:   Object.assign({}, state.room),
    layout: Object.assign({}, state.layout),
    users:  state.users
  };
}

export default connect(mapStateToProps)(UsersPanel);
