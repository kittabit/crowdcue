import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import Layout from "./Layout";
import Home from './Pages/Home';
import EventDetails from './Pages/EventDetails';
import Categories from './Pages/Categories';
import SingleCategory from './Pages/SingleCategory';
import Events from './Pages/Events';
import SingleVenue from './Pages/SingleVenue';
class App extends Component {
  render() {
    return (
    <BrowserRouter>
        <Routes>
          <Route exact path="/events/" element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="/events/categories" element={<Categories />} />
            <Route path="/events/category/:slug" element={<SingleCategory />} />
            <Route path="/events/all" element={<Events />} />
            <Route path="/events/details/:slug" element={<EventDetails />} />
            <Route path="/events/venue/:uuid" element={<SingleVenue />} />
          </Route>
        </Routes> 
    </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById('App'));