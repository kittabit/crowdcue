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
import ForYou from './Pages/ForYou';
import ReactGA from 'react-ga';

if(window.ogSettings.og_ga_ua){
  ReactGA.initialize(window.ogSettings.og_ga_ua);
}
class App extends Component {
  render() {

    const site_baseurl = document.getElementById('App').getAttribute('data-baseurl')

    return (
    <BrowserRouter basename={ site_baseurl }>
        <Routes>
          <Route path="/" element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="/categories" element={<Categories />} />
            <Route path="/category/:slug" element={<SingleCategory />} />
            <Route path="/all" element={<Events />} />
            <Route path="/for-you" element={<ForYou />} />
            <Route path="/details/:slug" element={<EventDetails />} />
            <Route path="/venue/:uuid" element={<SingleVenue />} />
          </Route>
        </Routes> 
    </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById('App'));