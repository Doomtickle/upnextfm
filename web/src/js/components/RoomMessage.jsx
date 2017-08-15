import React from 'react';
import Moment from 'react-moment';
import { ListItem } from 'material-ui/List';
import { UserPropType, MessagePropType } from 'utils/props';
import RoomUser from 'components/RoomUser';

const RoomMessage = ({ message, user, ...props }) => (
  <ListItem className="up-room-message" {...props}>
    <RoomUser user={user} />
    <Moment date={message.date} format="HH:mm" className="up-room-message__date" />
    <div className="up-room-message__message">
      {message.message}
    </div>

  </ListItem>
);

RoomMessage.propTypes = {
  message: MessagePropType.isRequired,
  user:    UserPropType.isRequired
};

export default RoomMessage;