import React, { Component } from 'react'
import { Link } from "react-router-dom";
import Header from '../Components/Header';
import EventCategorySmall from '../Components/EventCategorySmall';
import PersonalizedEvents from '../Components/PersonalizedEvents';
class Home extends Component {

    constructor (props){

        super(props);
        this.state = {
            user_personalized_events: [],
            user_personalized_events_count: 0,
        }
        
    }
    
    componentDidMount() {

        document.title = "Local Events";
        if(localStorage.getItem('og_user_flags') !== null){
            var og_user_flags = JSON.parse(localStorage.getItem('og_user_flags'));

            Promise.all([
                fetch('/wp-json/occasiongenius/v1/personalized?flags=' + og_user_flags.join(",")),
              ])
              .then(([res]) => Promise.all([res.json()]))
              .then(([data]) => this.setState({
                user_personalized_events: data.events,
                user_personalized_events_count: data.total
            }));

        }
    
    }   

    render(){

        return (
            <>
            
                <Header />

                {this.state.user_personalized_events_count === 4 &&
                    <>
                        <PersonalizedEvents events={ this.state.user_personalized_events } />
                    </>
                }

                {JSON.parse(window.ogSettings.og_featured_flags).map((item, index) => (
                  <>
                    <EventCategorySmall event_cat_id={item} key={index} />
                  </>
                ))}

                <div className="flex items-center flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6 lg:space-x-8 mt-8 md:mt-16">
                    <Link to="/events/categories" className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:bg-gray-600 hover:text-white no-underline text-center">
                        View All Categories
                    </Link>

                    <Link to="/events/all" className="block w-full md:w-3/5 border border-gray-800 text-base font-medium leading-none text-white uppercase py-6 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800 bg-gray-800 hover:bg-gray-600 hover:text-white no-underline text-center">
                        View All Events
                    </Link>                    
                </div>

            </>
        )
    }
}

export default Home
