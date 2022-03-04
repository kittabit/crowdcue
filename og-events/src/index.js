import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import Layout from "./Layout";
import Home from './Pages/home';
import EventDetails from './Pages/event_details';

class App extends Component {
  render() {
    return (
    <BrowserRouter>
        <Routes>
          <Route path="/events/" element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="/events/details/:slug" element={<EventDetails />} />
          </Route>
        </Routes>
    </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById('App'));