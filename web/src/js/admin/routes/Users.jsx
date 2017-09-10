import React from 'react';
import { Switch, Route } from 'react-router-dom';
import Index from './Users/Index';
import Edit from './Users/Edit';

const Users = () => (
  <Switch>
    <Route exact path="/users" component={Index} />
    <Route exact path="/users/:id" component={Edit} />
  </Switch>
);

export default Users;
